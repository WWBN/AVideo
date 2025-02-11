<?php

$config = dirname(__FILE__) . '/../videos/configuration.php';

require_once $config;

error_reporting(E_ALL); // Report all errors and warnings
ini_set('display_errors', 1); // Show errors on the page
ini_set('display_startup_errors', 1); // Show startup errors

// ✅ Restrict the base folder (e.g., "/videos/")
//$basePath = "/Audiofiles/";  // <-- CHANGE THIS to your target directory
$basePath = "";  // <-- CHANGE THIS to your target directory

if(empty($basePath)){
    forbiddenPage('Basepath is empty');
}

// ✅ Retrieve Bunny API Credentials from the Plugin Configuration
$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');
$parts = explode('.', $cdnObj->storage_hostname);
$apiAccessKey = $cdnObj->storage_password;
$storageZoneName = $cdnObj->storage_username;
$storageZoneRegion = trim(strtolower($parts[0]));
$pullZone = "https://{$cdnObj->storage_pullzone}"; // Bunny Pull Zone

// Get the requested path (default to basePath)
$path = isset($_GET['path']) ? $_GET['path'] : $basePath;

// ✅ Prevent directory traversal attacks ("../")
$path = preg_replace('/\.\.\/|\.\.\//', '', $path);

// ✅ Ensure users stay inside the allowed directory
if (strpos($path, $basePath) !== 0) {
    $path = $basePath;
}

// ✅ Bunny Storage API URL
$baseAPIURL = "https://storage.bunnycdn.com/$storageZoneName";
$url = $baseAPIURL . $path;

// ✅ Fetch files & directories from Bunny Storage using API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["AccessKey: $apiAccessKey"]);
$response = curl_exec($ch);
curl_close($ch);

// Decode JSON response
$files = json_decode($response, true);
if (!$files) {
    echo "<p>Error fetching files.</p>";
    exit;
}

// ✅ Breadcrumb Navigation (Restrict to $basePath)
echo "<h2>Browsing: $path</h2>";

// ✅ Back Button (Only works within $basePath)
if ($path !== $basePath) {
    $parentPath = dirname($path) . "/";
    if (strpos($parentPath, $basePath) === 0) {
        echo "<p><a href='?path=" . urlencode($parentPath) . "'>🔙 Back</a></p>";
    }
}

// ✅ List Directories
echo "<h3>📂 Directories</h3>";
$hasDirs = false;
foreach ($files as $file) {
    if ($file['IsDirectory']) {
        echo "<p><a href='?path=" . urlencode($path . $file['ObjectName'] . "/") . "'>📁 " . $file['ObjectName'] . "</a></p>";
        $hasDirs = true;
    }
}
if (!$hasDirs) {
    echo "<p>No directories found.</p>";
}

// ✅ List Files
echo "<h3>📄 Files</h3>";
$hasFiles = false;
foreach ($files as $file) {
    if (!$file['IsDirectory']) {
        $fileURL = $pullZone . $path . $file['ObjectName'];
        $fileSize = humanFileSize($file['Length']);
        echo "<p><a href='$fileURL' target='_blank'>📄 " . $file['ObjectName'] . " ($fileSize)</a></p>";
        $hasFiles = true;
    }
}
if (!$hasFiles) {
    echo "<p>No files found.</p>";
}

?>
