<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$videos = video::getAllVideosLight("", false, true);
foreach ($videos as $value) {
    if($value['type']!='video'){
        continue;
    }
    echo "\nStart: ".$value['title'];
    $videoFileName = $value['filename'];
    $destination = "{$global['systemRootPath']}videos/{$videoFileName}.webp";
    if (!file_exists($destination)) {
        echo "\nGet webp";
        $videosURL = getFirstVideoURL($videoFileName);
        $videoPath = getFirstVideoPath($videoFileName);
        $duration = (Video::getItemDurationSeconds(Video::getDurationFromFile($videoPath)) / 2);
        if (!empty($videosURL)) {
            $url = $videosURL;
            $file_headers = @get_headers($url);
            if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                echo "\nGet webp not found {$url}";
                continue;
            } else {
                $url = $config->getEncoderURL() . "getImageMP4/" . base64_encode($url) . "/webp/{$duration}";
                $image = url_get_contents($url);
                file_put_contents($destination, $image);
            }
        }else{            
            echo "\nVideo URL empty";
        }
        
        echo "\nGet done";
    }else{
        echo "\nFile exists: ".$value['title'];
    }
    
    echo "\nFinish: ".$value['title'];
    echo "\n******\n";
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
