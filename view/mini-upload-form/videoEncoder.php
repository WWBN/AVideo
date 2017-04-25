<?php

$configFile = '../../videos/configuration.php';
if (!file_exists($configFile)) {
    $configFile = '../videos/configuration.php';
}
require_once $configFile;

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
$videoResolution = $config->getVideo_resolution();

header('Content-Type: application/json');
$videoConverter = array();
//$videoConverter['mp4'] = ' -vf scale=' . $videoResolution . ' -vcodec h264 -acodec aac -strict -2 -y ';
//$videoConverter['webm'] = '-vf scale=' . $videoResolution . ' -f webm -c:v libvpx -b:v 1M -acodec libvorbis -y';
$videoConverter['mp4'] = $config->getFfmpegMp4();
$videoConverter['webm'] = $config->getFfmpegWebm();

$audioConverter = array();
//$audioConverter['mp3'] = ' -acodec libmp3lame -y ';
//$audioConverter['ogg'] = ' -acodec libvorbis -y ';
$audioConverter['mp3'] = $config->getFfmpegMp3();
$audioConverter['ogg'] = $config->getFfmpegOgg();

$filename = $argv[1];
$original_filename = "original_{$filename}";
$videoId = $argv[2];
$type = @$argv[3];
$status = 'a';

if ($type == 'audio' || $type == 'mp3' || $type == 'ogg') {
    foreach ($audioConverter as $key => $value) {
        if ($type !== 'audio' && $type != $key) {
            continue;
        }
        // convert video
        echo "\n\n--Converting audio {$key} \n";
        $pathFileName = "{$global['systemRootPath']}videos/{$original_filename}";
        $destinationFile = "{$global['systemRootPath']}videos/{$filename}.{$key}";
        eval('$ffmpeg ="'.$value.'";');
        $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.{$key} && rm -f {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt && {$ffmpeg}";
        echo "** executing command {$cmd}\n";
        exec($cmd . "  1> {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            echo "\\n **AUDIO ERROR**\n", print_r($output, true);
            if($status == 'a'){
                $status = 'x'.$key;
            }else{
                $status = 'x';
            }
        } else {
            echo "\n {$key} Ok\n";
        }
    }
}


if (empty($type) || $type == 'img') {
    //capture image
    echo "\n\n--Capture Image \n";
    
    $pathFileName = "{$global['systemRootPath']}videos/{$original_filename}";
    $destinationFile = "{$global['systemRootPath']}videos/{$filename}.jpg";
    eval('$ffmpeg ="'.$config->getFfmpegImage().'";');
    
    $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.jpg && {$ffmpeg}";
    echo "** executing command {$cmd}\n";
    exec($cmd . " 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "\\n**IMG ERROR**\n", print_r($output, true);
        /*
        if($status == 'a'){
            $status = 'ximg';
        }else{
            $status = 'x';
        }
         * 
         */
    } else {
        echo "\nImage Ok\n";
    }
}


foreach ($videoConverter as $key => $value) {
    if (!empty($type) && $type != $key) {
        continue;
    }
    // convert video
    echo "\n\n--Converting video {$key} \n";
    $pathFileName = "{$global['systemRootPath']}videos/{$original_filename}";
    $destinationFile = "{$global['systemRootPath']}videos/{$filename}.{$key}";
    eval('$ffmpeg ="'.$value.'";');
    $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.{$key} && rm -f {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt && {$ffmpeg}";
    echo "** executing command {$cmd}\n";
    exec($cmd . "  1> {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt  2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "\\n **VIDEO ERROR**\n", print_r($output, true);
        if($status == 'a'){
            $status = 'x'.$key;
        }else{
            $status = 'x';
        }
    } else {
        echo "\n {$key} Ok\n";
    }
}

// remove original file
//echo "Remove Original File\n";
//$cmd = "rm -f {$global['systemRootPath']}videos/{$original_filename}";
//exec($cmd);
// save status
echo "\n\n--Save Status\n";
require_once $global['systemRootPath'] . 'objects/video.php';
$video = new Video(null, null, $videoId);
$id = $video->setStatus($status);
