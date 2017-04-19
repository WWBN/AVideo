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
$videoConverter['mp4'] = ' -vf scale=' . $videoResolution . ' -vcodec h264 -acodec aac -strict -2 -y ';
$videoConverter['webm'] = '-vf scale=' . $videoResolution . ' -f webm -c:v libvpx -b:v 1M -acodec libvorbis -y';

$audioConverter = array();
$audioConverter['mp3'] = ' -acodec libmp3lame -y ';
$audioConverter['ogg'] = ' -acodec libvorbis -y ';

$filename = $argv[1];
$original_filename = "original_{$filename}";
$videoId = $argv[2];
$type = @$argv[3];
$error = false;

if ($type == 'audio' || $type == 'mp3' || $type == 'ogg') {
    foreach ($audioConverter as $key => $value) {
        if ($type !== 'audio' && $type != $key) {
            continue;
        }
        // convert video
        echo "\n\n--Converting audio {$key} \n";
        $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.{$key} && rm -f {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt && ffmpeg -i {$global['systemRootPath']}videos/{$original_filename} {$value} {$global['systemRootPath']}videos/{$filename}.{$key}";
        echo "** executing command {$cmd}\n";
        exec($cmd . "  1> {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt  2>&1", $output, $return_val);
        if ($return_val !== 0) {
            echo "\\n **AUDIO ERROR**\n", print_r($output, true);
            //$error = true;
            //exit;
        } else {
            echo "\n {$key} Ok\n";
        }
    }
}


if (empty($type) || $type == 'img') {
    //capture image
    echo "\n\n--Capture Image \n";
    $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.jpg && ffmpeg -ss 5 -i {$global['systemRootPath']}videos/{$original_filename} -qscale:v 2 -vframes 1 -y {$global['systemRootPath']}videos/{$filename}.jpg";
    echo "** executing command {$cmd}\n";
    exec($cmd . " 2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "\\n**IMG ERROR**\n", print_r($output, true);
        $error = true;
        //exit;
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
    $cmd = "rm -f {$global['systemRootPath']}videos/{$filename}.{$key} && rm -f {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt && ffmpeg -i {$global['systemRootPath']}videos/{$original_filename} {$value} {$global['systemRootPath']}videos/{$filename}.{$key}";
    echo "** executing command {$cmd}\n";
    exec($cmd . "  1> {$global['systemRootPath']}videos/{$filename}_progress_{$key}.txt  2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "\\n **VIDEO ERROR**\n", print_r($output, true);
        $error = true;
        //exit;
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
$id = $video->setStatus($error ? 'x' : 'a');
