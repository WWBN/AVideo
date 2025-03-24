<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface()) {
    forbiddenPage('Command line only');
}

ob_end_flush();

echo "📁 Checking for updates to yptsocket executable...\n";

$baseDir = __DIR__ . '/nodeSocket';
$remoteInfoURL = 'https://github.com/WWBN/AVideo-Socket/raw/refs/heads/main/dist/build-info.json?cb='.time();
$remoteBinaryURL = 'https://github.com/WWBN/AVideo-Socket/raw/refs/heads/main/dist/yptsocket?cb='.time();

$localInfoPath = $baseDir . '/build-info.json';
$localBinaryPath = $baseDir . '/yptsocket';

// Ensure nodeSocket folder exists
echo "📂 Ensuring 'nodeSocket' directory exists...\n";
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
    echo "✅ 'nodeSocket' directory created.\n";
}

// 1. Download remote build-info.json
echo "🌐 Downloading remote build-info.json...\n";
$remoteInfo = @file_get_contents($remoteInfoURL);
if ($remoteInfo === false) {
    die("❌ Failed to download remote build-info.json\n");
}
$remoteData = json_decode($remoteInfo, true);
if (!isset($remoteData['version'])) {
    die("❌ Invalid remote build-info.json format\n");
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
if ($remoteVersion > $localVersion) {
    echo "⬇️  New version available (local: $localVersion, remote: $remoteVersion). Starting update...\n";

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

// 4. Execute the binary
echo "🚀 Executing yptsocket with --force-kill-port...\n";
passthru(escapeshellcmd($localBinaryPath) . ' --force-kill-port');
