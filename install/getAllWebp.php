<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
$global['limitForUnlimitedVideos'] = -1;
$videos = video::getAllVideosLight("", false, true);
$count = 0;
foreach ($videos as $value) {
    $count++;
    echo "\n Start ($count) ******\n";
    if ($value['type'] != 'video') {
        echo "\nType ({$value['type']}) is not a video: " . $value['title'];
        echo "\n End ($count) ******\n";
        ob_flush();
        continue;
    }
    echo "\nStart: " . $value['title'];
    ob_flush();
    $videoFileName = $value['filename'];
    $destination = Video::getPathToFile("{$videoFileName}.webp");
    if (!file_exists($destination)) {
        echo "\nGet webp";
        ob_flush();
        $videosURL = getFirstVideoURL($videoFileName);
        $videoPath = getFirstVideoPath($videoFileName);
        $duration = (Video::getItemDurationSeconds(Video::getDurationFromFile($videoPath)) / 2);
        if (!empty($videosURL)) {
            $url = $videosURL;
            $file_headers = @get_headers($url);
            if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                echo "\nGet webp not found {$url}";
                ob_flush();
                continue;
            } else {
                $url = $config->getEncoderURL() . "getImageMP4/" . base64_encode($url) . "/webp/{$duration}";
                $image = url_get_contents($url);
                file_put_contents($destination, $image);
            }
        } else {
            echo "\nVideo URL empty";
            ob_flush();
        }

        echo "\nGet done";
        ob_flush();
    } else {
        echo "\nFile exists: " . $value['title'];
        ob_flush();
    }

    echo "\nFinish: " . $value['title'];
    echo "\n******\n";
    ob_flush();
}

function getFirstVideoURL($videoFileName) {
    $types = array('', '_Low', '_SD', '_HD');
    $videosList = getVideosURL($videoFileName);
    if (!empty($videosList['m3u8']["url"])) {
        return $videosList['m3u8']["url"];
    }
    foreach ($types as $value) {
        if (!empty($videosList['mp4' . $value]["url"])) {
            return $videosList['mp4' . $value]["url"];
        } else if (!empty($videosList['webm' . $value]["url"])) {
            return $videosList['webm' . $value]["url"];
        }
    }
    return false;
}

function getFirstVideoPath($videoFileName) {
    $types = array('', '_Low', '_SD', '_HD');
    $videosList = getVideosURL($videoFileName);
    if (!empty($videosList['m3u8']["path"])) {
        return $videosList['m3u8']["path"];
    }
    foreach ($types as $value) {
        if (!empty($videosList['mp4' . $value]["path"])) {
            return $videosList['mp4' . $value]["path"];
        } else if (!empty($videosList['webm' . $value]["path"])) {
            return $videosList['webm' . $value]["path"];
        }
    }
    return false;
}
