<?php

$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}
ob_end_flush();
$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

require_once './functions.php';

set_time_limit(300);
ini_set('max_execution_time', 300);

getConnID(0);
/*
$list = ftp_mlsd_recursive($conn_id[0], "/{$CDNObj->storage_username}/");
var_dump($list);
*/


$list = ftp_rawlist($conn_id[0], "/{$CDNObj->storage_username}/", true);
$count = 0;
foreach ($list as $value) {
    $count++;
    $parts = explode(' ', $value);
    $dir = end($parts);
    
    //echo $value.PHP_EOL;exit;
    echo $count.' Searching '."/{$CDNObj->storage_username}/{$dir}/".PHP_EOL;
    $files = ftp_rawlist($conn_id[0], "/{$CDNObj->storage_username}/{$dir}/", true);
    foreach ($files as $file) {
        if(preg_match('/enc_[0-9a-z].key$/i', $file)){
            echo '******** '.$file.PHP_EOL;
        }
    }
}