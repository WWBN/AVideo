<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$fileExtensions = array('jpg', 'gif', 'mp4', 'webm');


$files = array();

//foreach (glob("../videos/*.{" . implode(",", $fileExtensions) . "}", GLOB_BRACE) as $filename) {
foreach (glob("../videos/*", GLOB_BRACE) as $filename) {
    $base = basename($filename);
    if (is_dir($filename)) {
        if (strpos($base, "_YPTuniqid_") !== false) {
            $files[$base] = array($base, $filename);
        }
    } else {

        $baseName = explode("_portrait", $base);
        if (!empty($baseName[1])) {
            $files[$base] = array($baseName[0], $filename);
        } else {
            $baseName = explode("_thumbs", $base);
            if (!empty($baseName[1])) {
                $files[$base] = array($baseName[0], $filename);
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
    }
}
echo "*** Total filenames " . count($files) . "\n";
foreach ($files as $key => $value) {
    $video = Video::getVideoFromFileName($value[0], true);
    if (!empty($video)) {
        unset($files[$key]);
    }
}
echo "*** Total filenames " . count($files) . " Will be deleted\n";
$totalSize = 0;
foreach ($files as $key => $value) {
    $size = filesize($value[1]);
    $totalSize += $size;
    echo "{$value[0]} => $value[1] " . (humanFileSize($size)) . " \n";
}
echo "*** Confirm Delete Them (" . humanFileSize($totalSize) . ")? y/n: ";
ob_flush();
$confirm = trim(readline(""));
if (!empty($confirm) && strtolower($confirm) === 'y') {
    foreach ($files as $key => $value) {
        if (is_dir($value[1])) {
            rrmdir($value[1]);
            if (is_dir($value[1])) {
                echo "$value[1] Directory Deleted \n";
            } else {
                echo "$value[1] Directory Could Not be Deleted \n";
            }
        } else
        if (unlink($value[1])) {
            echo "$value[1] Deleted \n";
        } else {
            echo "$value[1] Could Not be Deleted \n";
        }
    }
}