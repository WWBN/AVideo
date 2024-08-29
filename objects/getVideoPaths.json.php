<?php
//error_reporting(0);
//php get_videos_paths.php example_video 0
header('Content-Type: application/json');

require_once __DIR__ . '/../videos/configuration.php';

if ($argc < 2) {
    die("Usage: php get_videos_paths.php <filename> [includeS3]\n");
}

// Get the command-line arguments
$filename = $argv[1];
$includeS3 = isset($argv[2]) ? (int)$argv[2] : 0;

// Define a unique lock file for this process
$lockFile = sys_get_temp_dir() . "/getVideosPaths_{$filename}_" . ($includeS3 ? 1 : 0) . ".lock";

// Check if the lock file is older than 1 minute
if (file_exists($lockFile) && (time() - filemtime($lockFile)) > 60) {
    unlink($lockFile); // Remove the old lock file
}

// Try to acquire a lock
$fp = fopen($lockFile, 'c');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
    die("Process is already running for {$filename} with includeS3={$includeS3}. Exiting.\n");
}

try {
    // Update the lock file's modification time to ensure it doesn't get removed due to age
    touch($lockFile);

    // Call the function
    $videos = Video::_getVideosPaths($filename, $includeS3);

    $cacheSuffix = "getVideosPaths_" . ($includeS3 ? 1 : 0);
    $videoCache = new VideoCacheHandler($filename, 0, true);
    $videoCache->setSuffix($cacheSuffix);
    $response = $videoCache->setCache($videos);

    echo json_encode(array($response));
    /*
    $global['forceGetCache'] = 1;
    $videoCache = new VideoCacheHandler($filename, 0, true);
    $cache = $videoCache->getCache($cacheSuffix, 0);
    echo json_encode(array($response,  $cache, ObjectYPT::getLastUsedCacheInfo()));
    */
} catch (Exception $e) {
    error_log("Error processing video paths: " . $e->getMessage());
} finally {
    // Release the lock and delete the lock file
    flock($fp, LOCK_UN);
    fclose($fp);
    unlink($lockFile);
}
