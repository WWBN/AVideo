<?php

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;
error_reporting(E_ALL);

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

$S3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');

if (empty($S3)) {
    return die('Plugin S3 disabled');
}
ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;
$total = count($videos);

echo "{$count}/{$total} Start" . PHP_EOL;

foreach ($videos as $key => $value) {
    $count++;

    echo "{$count}/{$total} checking [{$value['id']}] {$value['title']}" . PHP_EOL;
    $destination = Video::getPathToFile($value['filename'], true);

    foreach (glob($destination . '*.{mp3,mp4,webm}', GLOB_BRACE) as $file) {
        $filesize =  filesize($file);
        $filesizeHuman =  humanFileSize($filesize);
        echo "{$count}/{$total} checking [{$value['id']}] {$value['title']} {$file} [$filesizeHuman]" . PHP_EOL;
        if ($filesize && isDummyFile($file)) {
            echo "{$count}/{$total} Downloading [{$value['id']}] {$value['title']}" . PHP_EOL;
            $filename = basename($file);
            if ($S3->copy_from_s3($filename, $file)) {
                $filesize =  filesize($file);
                $filesizeHuman =  humanFileSize($filesize);
                echo "{$count}/{$total} SUCCESS [{$value['id']}] {$value['title']} [$filesizeHuman]" . PHP_EOL;
            } else {
                echo "{$count}/{$total} FAIL [{$value['id']}] {$value['title']}" . PHP_EOL;
            }
        } else {
            echo "{$count}/{$total} Not Dummy [{$value['id']}] {$value['title']} $file " . PHP_EOL;
        }
    }
}

echo "{$count}/{$total} END" . PHP_EOL;
echo PHP_EOL . " Done! " . PHP_EOL;
die();
