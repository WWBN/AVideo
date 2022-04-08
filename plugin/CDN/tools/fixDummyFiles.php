<?php
$config = dirname(__FILE__) . '/../../../videos/configuration.php';
require_once $config;

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$isCDNEnabled = AVideoPlugin::isEnabledByName('CDN');

if (empty($isCDNEnabled)) {
    return die('Plugin disabled');
}

$index = intval(@$argv[1]);

ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$tenMB = 102400;

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id DESC ";
$res = sqlDAL::readSql($sql);
$videos = sqlDAL::fetchAllAssoc($res);
$total = count($videos);
sqlDAL::close($res);
foreach ($videos as $key => $value) {
    if ($value['status'] === Video::$statusActive) {
        if (empty($value['sites_id'])) {
            continue;
        }
        
        $dir = "{$path}{$value['filename']}/";
        
        $dirsize = getDirSize($dir);
        if($dirsize<$tenMB){
            echo "Directory too small {$dir}  $dirsize<$tenMB" . PHP_EOL;
            continue;
        }else{
            echo "Directory size is {$dir}  $dirsize" . PHP_EOL;
        }
        
        $filesAffected = CDNStorage::createDummyFiles($value['id']);
        if (empty($filesAffected)) {
            echo "{$key}/{$total} ERROR " . PHP_EOL;
        } else {
            echo "{$key}/{$total} filesAffected={$filesAffected} " . PHP_EOL;
        }
    }
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
