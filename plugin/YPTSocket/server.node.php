<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

ob_end_flush();

echo "📁 Checking for updates to yptsocket executable...\n";

$baseDir = __DIR__ . '/nodeSocket';
$remoteInfoURL = 'https://github.com/WWBN/AVideo-Socket/raw/refs/heads/main/dist/build-info.json';
$remoteBinaryURL = 'https://github.com/WWBN/AVideo-Socket/raw/refs/heads/main/dist/yptsocket';

$localInfoPath = $baseDir . '/build-info.json';
$localBinaryPath = $baseDir . '/yptsocket';

// Ensure nodeSocket folder exists
echo "📂 Ensuring 'nodeSocket' directory exists...\n";
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
    echo "✅ 'nodeSocket' directory created.\n";
}

// 1. Download remote build-info.json
echo "🔍 Diagnostics before download:\n";
echo "   📌 URL: $remoteInfoURL\n";
echo "   📌 allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";
echo "   📌 openssl extension: " . (extension_loaded('openssl') ? 'loaded' : 'NOT loaded') . "\n";
echo "   📌 PHP version: " . PHP_VERSION . "\n";

// Test DNS resolution
$parsed = parse_url($remoteInfoURL);
$host = $parsed['host'] ?? '';
$ip = @gethostbyname($host);
echo "   📌 DNS resolve '{$host}': " . ($ip !== $host ? $ip : 'FAILED') . "\n";

echo "🌐 Downloading remote build-info.json...\n";

// Try with stream context for better error reporting
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'follow_location' => true,
        'max_redirects' => 10,
        'user_agent' => 'AVideo-Updater/1.0',
    ],
    'ssl' => [
        'verify_peer' => true,
        'verify_peer_name' => true,
    ],
]);

$remoteInfo = @file_get_contents($remoteInfoURL, false, $context);

// Log response headers if available
if (isset($http_response_header) && is_array($http_response_header)) {
    echo "   📌 Response headers:\n";
    foreach ($http_response_header as $header) {
        echo "      $header\n";
    }
} else {
    echo "   📌 No response headers received (connection may have failed entirely)\n";
}

if ($remoteInfo === false) {
    $err = error_get_last();
    echo "   📌 Last PHP error: " . ($err ? $err['message'] : 'none') . "\n";

    // Try curl as fallback diagnostic
    if (function_exists('curl_init')) {
        echo "   📌 Attempting curl diagnostic...\n";
        $ch = curl_init($remoteInfoURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'AVideo-Updater/1.0');
        $curlResult = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlEffectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        echo "   📌 curl HTTP code: $curlHttpCode\n";
        echo "   📌 curl effective URL: $curlEffectiveUrl\n";
        if ($curlError) {
            echo "   📌 curl error: $curlError\n";
        }
        if ($curlResult !== false && !empty($curlResult)) {
            echo "   📌 curl got content (" . strlen($curlResult) . " bytes), using it as fallback\n";
            $remoteInfo = $curlResult;
        }
    } else {
        echo "   📌 curl extension not available for fallback\n";
    }
}

if ($remoteInfo === false) {
    die("❌ Failed to download remote build-info.json\n");
}
$remoteData = json_decode($remoteInfo, true);
if (!isset($remoteData['version'])) {
    die("❌ Invalid remote build-info.json format\n");
} else {
    echo "🌐 Found remote {$remoteInfo}" . PHP_EOL;
}

// 2. Read local build-info.json
echo "📄 Reading local build-info.json...\n";
$localData = [];
if (file_exists($localInfoPath)) {
    $localInfo = file_get_contents($localInfoPath);
    $localData = json_decode($localInfo, true);
}

$localVersion = isset($localData['version']) ? (int)$localData['version'] : 0;
$remoteVersion = (int)$remoteData['version'];

// 3. Compare and update if needed
if ($remoteVersion != $localVersion) {
    echo "⬇️  New version available (local: $localVersion, remote: $remoteVersion). Starting update...\n";

    // Kill existing yptsocket process
    echo "🛑 Checking for running yptsocket process...\n";
    $pidOutput = [];
    exec("pgrep -f '$localBinaryPath'", $pidOutput);
    if (!empty($pidOutput)) {
        foreach ($pidOutput as $pid) {
            echo "🔪 Killing yptsocket process with PID: $pid\n";
            exec("kill -9 $pid");
        }
    } else {
        echo "✅ No running yptsocket process found.\n";
    }

    // Download new binary
    echo "⬇️  Downloading new yptsocket binary...\n";
    $binary = @file_get_contents($remoteBinaryURL);
    if ($binary === false) {
        die("❌ Failed to download yptsocket binary\n");
    }
    file_put_contents($localBinaryPath, $binary);
    chmod($localBinaryPath, 0755);
    echo "✅ Binary downloaded and made executable: $localBinaryPath\n";

    // Save updated build-info.json
    file_put_contents($localInfoPath, $remoteInfo);
    echo "✅ build-info.json updated at: $localInfoPath\n";
} else {
    echo "🆗 Local version is up to date (local: $localVersion, remote: $remoteVersion)\n";
}

// 🔥 Kill PHP worker processes before starting socket
echo "🛑 Checking for running PHP worker processes...\n";
$phpWorkerOutput = [];
exec("pgrep -f 'php " . __DIR__ . "/worker.php'", $phpWorkerOutput);
if (!empty($phpWorkerOutput)) {
    foreach ($phpWorkerOutput as $pid) {
        echo "🔪 Killing PHP worker process with PID: $pid\n";
        exec("kill -9 $pid");
    }
} else {
    echo "✅ No running PHP worker process found.\n";
}

// 4. Execute the binary
echo "🚀 Executing yptsocket with --force-kill-port...\n";
passthru(escapeshellcmd($localBinaryPath) . ' --force-kill-port');
