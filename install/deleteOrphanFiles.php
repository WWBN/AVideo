<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$doNotDeleteFilesList = array('configuration.php', 'favicon.ico', 'favicon.png', 'avideo.log', 'PayPal.log', 'socketPID.log', 'logo.png', 'logoOverlay.png');

$lockFilename = '.move_v1.lock';
$path = getVideosDir();
$files = array_diff(scandir($path), array('.', '..'));
echo "*** Total filenames " . count($files) . "\n";
foreach ($files as $key => $value) {
    $dir = "{$path}{$value}";
    if (!is_dir($dir)) {
        $bname = basename($dir);
        if (in_array($bname, $doNotDeleteFilesList) || preg_match('/configuration\./i', $bname)) {
            unset($files[$key]);
        } else {
            $filename = Video::getCleanFilenameFromFile($dir);
            $video = Video::getVideoFromFileName($filename, true);
            if (!empty($video)) {
                //echo "+++ Video FOUND for filename {$filename} ".PHP_EOL;
                unset($files[$key]);
            } else {
                $files[$key] = array($value, $dir);
                //echo "*** Video NOT found for filename {$filename} ".PHP_EOL;
            }
        }
        continue;
    }
    $file = "{$dir}" . DIRECTORY_SEPARATOR . "{$lockFilename}";
    if (file_exists($file)) {
        $filename = Video::getCleanFilenameFromFile($dir);
        $video = Video::getVideoFromFileName($filename, true);
        if (!empty($video)) {
            //echo "+++ Video FOUND for filename {$filename} ".PHP_EOL;
            unset($files[$key]);
        } else {
            $files[$key] = array($value, $dir);
            //echo "*** Video NOT found for filename {$filename} ".PHP_EOL;
        }
    } else {
        //echo "*** Lock file does not exists {$file} ".PHP_EOL;
        unset($files[$key]);
    }
}

$total = count($files);
echo "*** Total filenames " . $total . " Will be deleted\n";

if (empty($total)) {
    exit;
}

$totalSize = 0;
foreach ($files as $key => $value) {
    $size = getDirSize($value[1]);
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
