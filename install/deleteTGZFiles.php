<?php
//streamer config
require_once '../videos/configuration.php';
ob_end_flush();
if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$path = getVideosDir();

$files = array_diff(scandir($path), ['.', '..']);
foreach ($files as $value) {
    $dir = "{$path}{$value}";
    if (is_dir($dir)) {
        $files2 = array_diff(scandir($dir), ['.', '..']);
        foreach ($files2 as $value2) {
            $ext = pathinfo($value2, PATHINFO_EXTENSION);
            if ($ext=='tgz') {
                $file = "{$dir}/{$value2}";
                echo $file.' '. humanFileSize(filesize($file)).PHP_EOL;
                unlink($file);
            }
        }
    }
}
