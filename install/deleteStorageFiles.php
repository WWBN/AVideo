<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$p = AVideoPlugin::loadPluginIfEnabled('YPTStorage');
if(empty($p)){
    return die('YPTStorage plugin disabled');
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
$max = 2;
$count = 0;
foreach ($files as $key => $value) {
    $getUsageFromFilename = YPTStorage::getUsageFromFilename($value[0]);
    echo "Local file videos_id = {$value[0]}=>  $getUsageFromFilename ". humanFileSize($getUsageFromFilename)."\n";
        
    if($getUsageFromFilename<2000){
        echo "Local file is too small, probably transfered already \n";
        continue;
    }
    $count++;
    if($count>$max){
        exit;
    }
    $video = Video::getVideoFromFileName($value[0], true);
    if (!empty($video)) {
        $sites_id = $video['sites_id'];
        if($sites_id>0){
            if($sites_id>0 && YPTStorage::checkIfFileSizeIsTheSame($video['id'], -1, $sites_id)){
                //YPTStorage::createDummyHLS($video['id']);
                echo "File size is the same videos_id = {$video['id']}\n";
            }else{
                echo "ERROR File size is NOT the same videos_id = {$video['id']} {$sites_id}\n";
            }
        }
    }
}