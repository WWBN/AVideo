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

$path = getVideosDir();
ob_end_flush();
set_time_limit(300);
ini_set('max_execution_time', 300);

$tenMB = 10240000;

$sql = "SELECT * FROM  videos WHERE 1=1 ORDER BY id DESC ";
$res = sqlDAL::readSql($sql);
$videos = sqlDAL::fetchAllAssoc($res);
$total = count($videos);
$client = CDNStorage::getStorageClient();
sqlDAL::close($res);
foreach ($videos as $key => $value) {
    $videos_id = $value['id'];
    if ($value['status'] === Video::$statusActive) {
        if (empty($value['sites_id'])) {
            continue;
        }

        $mp4 = "{$path}{$value['filename']}/index.mp4";

        if(file_exists($mp4)){
            $fileInfo = CDNStorage::getFilesListInfo($mp4, $videos_id);
            $filesize = $fileInfo['local_filesize'] ?? 0;
            if($fileInfo){
                var_dump($fileInfo);
            }
        }
    }
}

echo PHP_EOL . " Done! " . PHP_EOL;
die();
