<?php
//streamer config
require_once '../videos/configuration.php';

if(!isCommandLineInterface()){
    return die('Command Line only');
}
echo "Enter the new Streamer URL or press enter to skip:";
echo "\n";
ob_flush();
$streamerURL = trim(readline(""));

if(!empty($streamerURL)){
    if (substr($streamerURL, -1) !== '/') {
       $streamerURL.="/";
    }
    $global['webSiteRootURL'] = $streamerURL;
    echo "Rewrite Streamer Config File\n";
    // change the streamer config file
    Configuration::rewriteConfigFile();
    
    echo "Rewrite Streamer Config File - DONE\n";
}

$encoderConfigFile = "{$global['systemRootPath']}Encoder/videos/configuration.php";

echo "Checking encoder in {$encoderConfigFile}\n";
if(file_exists($encoderConfigFile)){
    echo "Encoder found in {$encoderConfigFile}\n";
    require_once $encoderConfigFile;
    // change the encoder database for admin user
    echo "Encoder Update configurations set allowedStreamersURL\n";
    $sql = "update configurations set allowedStreamersURL = '{$streamerURL}';";
    $global['mysqli']->query($sql);
    echo "Encoder Update streamers set siteURL\n";
    $sql = "update streamers set siteURL = '{$streamerURL}';";
    $global['mysqli']->query($sql);
    // change the encoder config file
}else{
    echo "Encoder not found in {$encoderConfigFile}\n";
}



