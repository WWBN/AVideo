<?php
//error_reporting(0);
//php get_videos_paths.php example_video 0
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

if ($argc < 2) {
    die("Usage: php get_videos_paths.php <filename> [includeS3]\n");
}

// Get the command-line arguments
$filename = $argv[1];
$includeS3 = isset($argv[2]) ? (int)$argv[2] : 0;

// Call the function
$videos = Video::_getVideosPaths($filename, $includeS3);

$cacheSuffix = "getVideosPaths_" . ($includeS3 ? 1 : 0);
$videoCache = new VideoCacheHandler($filename);
$videoCache->setCache($videos);

