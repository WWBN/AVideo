<?php
if (!php_sapi_name() === 'cli') {
    die('Command Line only');
}

function humanFileSize($size, $unit = ""){
    if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
        return number_format($size / (1 << 30), 2) . "GB";
    }

    if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
        return number_format($size / (1 << 20), 2) . "MB";
    }

    if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
        return number_format($size / (1 << 10), 2) . "KB";
    }

    return number_format($size) . " bytes";
}

set_time_limit(300);
ini_set('max_execution_time', 300);
$glob = glob(sys_get_temp_dir()."/*");
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
