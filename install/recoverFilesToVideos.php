<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$fileExtensions = array('mp4', 'webm', 'm3u8');


$files = array();

//foreach (glob("../videos/*.{" . implode(",", $fileExtensions) . "}", GLOB_BRACE) as $filename) {
foreach (glob("../videos/*", GLOB_BRACE) as $filename) {
    $base = basename($filename);
    if (is_dir($filename)) {
        if (strpos($base, "_YPTuniqid_") !== false) {
            $files[$base] = array($base, $filename);
        }
    } else {
        $types = array('_HD', '_Low', '_SD');
        $notFound = true;
        foreach ($types as $value) {
            $baseName = explode($value, $base);
            if (!empty($baseName[1])) {
                $files[$base] = array($baseName[0], $filename);
                $notFound = false;
            }
        }
        if ($notFound) {
            foreach ($fileExtensions as $value) {
                if (strpos($base, ".$value") === false) {
                    continue;
                }
                $baseName = str_replace("." . $value, "", $base);
                if (!empty($baseName[1])) {
                    if (!in_array($baseName, $files)) {
                        $files[$base] = array($baseName, $filename);
                    }
                }
            }
        }
    }
}
$total = count($files);
echo "*** Total filenames " . $total . "\n";
foreach ($files as $key => $value) {
    $video = Video::getVideoFromFileName($value[0], true);
    if (!empty($video)) {
        unset($files[$key]);
    }
}
echo "*** Total filenames " . $total . " Will be created\n";
echo "*** Confirm Create Them? y/n: ";
ob_flush();
$confirm = trim(readline(""));
if (!empty($confirm) && strtolower($confirm) === 'y') {
    $count = 0;
    foreach ($files as $key => $value) {
        $count++;
        $title = "Video recovered: ".date("Y-m-d H:i:s", filectime($value[1]));
        $video = new Video($title, $value[0]);
        $video->setStatus(Video::$statusActive);
        $video->setUsers_id(1);
        if($video->save(false, true)){
            echo "{$count}/{$total} {$title} created\n";
        }else{
            echo "{$count}/{$total} ERROR on create video {$title}\n";
        }
    }
}