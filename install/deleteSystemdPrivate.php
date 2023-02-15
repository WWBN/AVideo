<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

set_time_limit(300);
ini_set('max_execution_time', 300);
$glob = glob("/tmp/systemd-private-*-apache2.service-*/tmp/*");
$totalItems = count($glob);
$one_day_ago = time() - (24 * 60 * 60); // timestamp of 1 day ago
echo "Found total of {$totalItems} items " . PHP_EOL;
$countItems = 0;
$totalFilesize = 0;
foreach ($glob as $file) {
    $countItems++;
    if (filemtime($file) < $one_day_ago) {
        $size = filesize($file);
        $humanFSize = humanFileSize($size);
        echo "delete {$humanFSize} $file" . PHP_EOL;
        $totalFilesize += $size;
        unlink($file);
    }
}

$humanFSize = humanFileSize($totalFilesize);
echo " ----- " . PHP_EOL;
echo "Total deleted {$humanFSize}" . PHP_EOL;
