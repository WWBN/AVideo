<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

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
    echo "Found total of {$totalItems} items " . PHP_EOL;
    $dirname = $basename.DIRECTORY_SEPARATOR;
    $countItems = 0;
    foreach ($glob as $file) {
        $countItems++;
        echo "[$countItems/$totalItems] Process file {$file} " . PHP_EOL;
        if (is_dir($file)) {
            if(!$isStorage){
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
        rename($file, $newname);
        echo "{$count}/{$total} move $filename to $newname" . PHP_EOL;
    }
    ob_flush();
}
echo PHP_EOL." Deleting cache ... ";
ObjectYPT::deleteALLCache();
echo PHP_EOL." Done! ";
die();




