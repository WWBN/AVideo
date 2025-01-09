<?php
// Ensure the script is being run from the command line
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

// Set the directory pattern to search for
$baseDir = '/tmp/systemd-private-*/tmp';

// Find all matching directories
$dirs = glob($baseDir, GLOB_ONLYDIR);

// Check if there are any matching directories
if (empty($dirs)) {
    echo "No matching directories found.\n";
    exit(0);
}

$oneDayAgo = time() - (24 * 60 * 60); // Timestamp for 1 day ago

foreach ($dirs as $dir) {
    echo "Processing directory: $dir\n";

    // Get all files in the directory
    $files = glob("$dir/*");
    foreach ($files as $file) {
        // Check if it's a file and if it's older than 1 day
        if (is_file($file) && filemtime($file) < $oneDayAgo) {
            echo "Deleting file: $file\n";
            unlink($file);
        }
    }
}

echo "Cleanup complete.\n";
