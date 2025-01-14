<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$obj = AVideoPlugin::getDataObjectIfEnabled('WebRTC');

if (empty($obj)) {
    return die('Plugin disabled');
}

// Define file paths
$availableFilePath = WebRTC::getWebRTC2RTMPAssetVersionFile();
$sourceExecutablePath = WebRTC::getWebRTC2RTMPAssetFile();
$executablePath = WebRTC::getWebRTC2RTMPFile();
$currentFilePath = WebRTC::getWebRTC2RTMPJsonFile();
$log = WebRTC::getWebRTC2RTMPLogFile();

// Helper function to read JSON files
function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }
    $content = file_get_contents($filePath);
    return json_decode($content, true);
}

// Main script
try {
    // Read the JSON files
    $currentData = readJsonFile($currentFilePath);
    $availableData = readJsonFile($availableFilePath);

    // Display versions
    echo "Current version: " . $currentData['version'] . PHP_EOL;
    echo "Available version: " . $availableData['version'] . PHP_EOL;

    // Compare versions
    if ($currentData['version'] !== $availableData['version']) {
        echo "A new version is available. Do you want to update? (yes/no): ";
        $input = trim(fgets(STDIN));

        if (strtolower($input) === 'yes') {
            // Execute update commands
            echo "Stopping the current server..." . PHP_EOL;
            exec("pkill WebRTC2RTMP", $output, $status);

            if ($status !== 0) {
                echo "Warning: Could not stop the server or it was not running." . PHP_EOL;
            }

            echo "Removing old executable..." . PHP_EOL;
            unlink($executablePath);

            echo "Copying new executable..." . PHP_EOL;
            copy($sourceExecutablePath, $executablePath);

            echo "Making new executable runnable..." . PHP_EOL;
            chmod($executablePath, 0755);

            echo "Starting the new server..." . PHP_EOL;

            $command = "{$executablePath} --port={$obj->port} > $log ";
            return execAsync($command);
            echo "Update completed successfully!" . PHP_EOL;
        } else {
            echo "Update canceled." . PHP_EOL;
        }
    } else {
        echo "You are already running the latest version." . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
