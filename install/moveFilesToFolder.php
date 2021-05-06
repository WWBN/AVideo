<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

if(isAnyStorageEnabled()){
    die('This may bring you problems if you are using S3 or B2 or FTP or YPTstorage, but if you are sure what you are doing, you can comment this line and run this script');
}

$path = getVideosDir();
$total = Video::getTotalVideos("", false, true, true, false, false);
$videos = Video::getAllVideosLight("", false, true, false);
$count = 0;
foreach ($videos as $value) {
    $count++;
    
    $basename = "{$path}{$value['filename']}";
    $glob = glob("{$basename}*");
    $dirname = $basename.DIRECTORY_SEPARATOR;
    foreach ($glob as $file) {
        if (is_dir($file)) {
            //echo $file.PHP_EOL;
            $move = Video::updateDirectoryFilename($file);
            echo "-->".PHP_EOL." {$count}/{$total} move directory {$move['oldDir']} to {$move['newDir']} ".PHP_EOL."<--" . PHP_EOL . PHP_EOL;
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




