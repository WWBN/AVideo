<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$p = AVideoPlugin::loadPluginIfEnabled('YPTStorage');
if (empty($p)) {
    return die('YPTStorage plugin disabled');
}

$fileExtensions = array('jpg', 'gif', 'mp4', 'webm', 'tgz');


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
$max = 10;
$count = 0;
$countExecuted = 0;
$checkedFiles = array();
foreach ($files as $key => $value) {
    if (!empty($checkedFiles[$value[0]])) {
        continue;
    }
    $checkedFiles[$value[0]] = array(true);
    $getUsageFromFilename = YPTStorage::getUsageFromFilename($value[0]);
    $checkedFiles[$value[0]][] = $getUsageFromFilename;

    if ($getUsageFromFilename < 200000) {
        //echo "Local file is too small, probably transfered already or is a directory (HLS) \n";
        continue;
    }
    $video = Video::getVideoFromFileName($value[0], true);
    if (!empty($video)) {
        $sites_id = $video['sites_id'];
        if ($sites_id > 0) {
            $count++;
            if ($count > $max) {
                exit;
            }
            echo "{$count}: Local file videos_id = {$video['id']} {$video['title']}=>  $getUsageFromFilename " . humanFileSize($getUsageFromFilename) . "\n";
            $source_size = YPTStorage::getFileSize($video['id'], -1);
            $destination_size = YPTStorage::getFileSize($video['id'], $sites_id);
            if (!empty($destination_size) && $destination_size > 5000000 && $source_size <= $destination_size) {
                $countExecuted++;
                if ($countExecuted > $max) {
                    exit;
                }
                YPTStorage::createDummy($video['id']);
                $tgzFile = $global['systemRootPath'] . "videos/{$video['filename']}.tgz";
                if(file_exists($tgzFile)){
                    unlink($tgzFile);
                }
                echo "******   File size is the same videos_id = {$video['id']} {$sites_id} [$source_size!==$destination_size][" . humanFileSize($source_size) . "!==" . humanFileSize($destination_size) . "]\n";
                //exit;
            } else if($source_sizee > 5000000){
                echo "----- ERROR File size is NOT the same videos_id and it suppose to be on the storage = {$video['id']} {$sites_id} [$source_size!==$destination_size][" . humanFileSize($source_size) . "!==" . humanFileSize($destination_size) . "]\n";
            } else if($source_sizee > 5000000){
                echo "+++++ All seems fine with video {$video['id']} {$sites_id} [$source_size!==$destination_size][" . humanFileSize($source_size) . "!==" . humanFileSize($destination_size) . "]\n";
            }
        } else {
            //echo "The video_id {$video['id']} ({$video['title']}) is not hosted on the storage\n";
        }
    }
}