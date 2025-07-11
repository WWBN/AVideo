<?php
//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$basePath = getVideosDir();

// Ensure the directory exists
if (!is_dir($basePath)) {
    die("Directory does not exist: $basePath\n");
}

ob_end_flush();

// Scan the base directory
$folders = scandir($basePath);
$count = 0;

$countDirs = count($folders);

// Loop through each item in the directory
foreach ($folders as $folder) {
    // Skip special directories "." and ".."
    if ($folder === '.' || $folder === '..' || preg_match('/_converted.mp4/', $folder)) {
        continue;
    }
    $count++;

    // Build the full path
    $fullPath = $basePath . $folder;
    // Check if it's a directory and starts with "v_" or "video_"
    if (is_dir($fullPath) && (str_starts_with($folder, 'v_') || str_starts_with($folder, 'video_'))) {
        $info = "[{$count}/{$countDirs}] ";
        $video = Video::getVideoFromFileNameLight($folder);
        if (empty($video)) {
            $parts = explode('_', $folder);
            $date = $parts[1];
            preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', $date, $matches);
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            $second = $matches[6];

            $mysqlDate = "20{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}";

            $new_video = new Video($folder, $folder, 0, false);
            $new_video->setCreated($mysqlDate);
            $new_video->setCategories_id(1);
            $new_video->setUsers_id(1);
            $new_video->setStatus(Video::STATUS_ACTIVE);
            $new_video->setType(Video::$videoTypeVideo);

            $id = $new_video->save(false, true);
            if ($id) {
                echo "$info $mysqlDate [{$folder}] id={$id} line=".__LINE__  . PHP_EOL;
            } else {
                echo "$info $mysqlDate [{$folder}] ERROR line=".__LINE__  . PHP_EOL;
            }
        }
    }
}
