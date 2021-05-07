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
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;

$isStorage = isAnyStorageEnabled();

foreach ($videos as $value) {
    $count++;
    
    $basename = "{$path}{$value['filename']}";
    echo " {$count}/{$total} Searching {$basename} ".PHP_EOL;
    $glob = glob("{$basename}*");
    $totalItems = count($glob);
    if($totalItems){
        echo "Creating dir {$basename} " . PHP_EOL;
        make_path(addLastSlash($basename));
    }
    echo "Found total of {$totalItems} items " . PHP_EOL;
    $dirname = $basename.DIRECTORY_SEPARATOR;
    $countItems = 0;
    foreach ($glob as $file) {
        $countItems++;
        echo "[$countItems/$totalItems] Process file {$file} " . PHP_EOL;
        if (is_dir($file)) {
            if(!$isStorage && !Video::isNewVideoFilename($move['oldDir'])){
                //echo $file.PHP_EOL;
                $move = Video::updateDirectoryFilename($file);
                echo "-->".PHP_EOL." {$count}/{$total} move directory {$move['oldDir']} to {$move['newDir']} ".PHP_EOL."<--" . PHP_EOL . PHP_EOL;
            }else{
                echo " We will not rename directory {$file} ".PHP_EOL;
            }
            continue;
        }
        $filename = basename($file);
        $newname = Video::getPathToFile($filename);
        $renamed = rename($file, $newname);
        if($renamed){
            echo "{$count}/{$total} moved $filename to $newname" . PHP_EOL;
        }else{
            echo "{$count}/{$total} fail to move $filename to $newname" . PHP_EOL;
        }
    }
    ob_flush();
}
echo PHP_EOL." Deleting cache ... ";
ObjectYPT::deleteALLCache();
$videosDir = Video::getStoragePath(); 
exec("chown -R www-data:www-data {$videosDir}");
exec("chmod -R 755 {$videosDir}");
echo PHP_EOL." Done! ".PHP_EOL;
die();

