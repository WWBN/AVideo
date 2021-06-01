<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

set_time_limit(300);
ini_set('max_execution_time', 300);

$global['rowCount'] = $global['limitForUnlimitedVideos'] = 999999;
$path = getVideosDir();
$logFile = $global['logfile'];
echo "Open $logFile" . PHP_EOL;
$handle = fopen($logFile, "r");

$pattern = '/Video::updateDirectoryFilename video folder renamed from \[olddir=(.+)\] \[newdir=(.+)\]/';

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (preg_match($pattern, $line, $matches)) {
            //var_dump($matches);
            if(!is_dir($matches[2])){
                continue;
            }
            $glob = glob("{$matches[1]}*");
            $totalItems = count($glob);
            echo "Found total of {$totalItems} items " . PHP_EOL;
            $countItems = 0;
            foreach ($glob as $file) {
                if(is_dir($file)){
                    continue;
                }
                
                $pathInfo = pathinfo($file);
                $sourceFilename = Video::getCleanFilenameFromFile($file);
                $filename = Video::getCleanFilenameFromFile($matches[2]);
                
                $basename = str_replace($sourceFilename, $filename, $pathInfo['basename']);
                
                $destinationFile = "{$matches[2]}{$basename}";
                
                //var_dump($pathInfo, $basename,$filename, $sourceFilename, $destinationFile);
                $countItems++;
                echo "[$countItems/$totalItems] move file {$file} to {$destinationFile}" . PHP_EOL;
                rename($file, $destinationFile);
                
            }
        }
        // process the line read.
    }

    fclose($handle);
} else {
    // error opening the file.
} 