<?php

header('Content-Type: application/json');
$cachedFile = '../videos/cache/version.cache';

if(empty($_GET['modified']) && file_exists($cachedFile)){
    $content = file_get_contents($cachedFile);
    $json = json_decode($content);
    if(!empty($json)){
        $json->cache = filectime($cachedFile);
        echo json_encode($json);
        exit;
    }
}

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';

$obj = new stdClass();

$obj->title = $config->getWebSiteTitle();
$obj->url = $global['webSiteRootURL'];
$obj->language = $config->getLanguage();
$obj->version = $config->getVersion();
$obj->date = date("Y-m-d H:i:s");
$obj->MySQLDate = getMySQLDate();
$obj->version = $config->getVersion();
$obj->plugins = Plugin::getAvailablePluginsBasic();
if (empty($_GET['version'])) {
    $obj->videos = array();
    //$_GET['modified'] = "2018-03-13 15:46:57";
    $_REQUEST['rowCount'] = 100;
    $videos = Video::getAllVideos();

    foreach ($videos as $key => $value) {
        $vid = new stdClass();
        $vid->id = $value['id'];
        $vid->title = $value['title'];
        $vid->clean_title = $value['clean_title'];
        $vid->views_count = $value['views_count'];
        $vid->category_name = $value['category'];
        $vid->likes = $value['likes'];
        $vid->dislikes = $value['dislikes'];
        $vid->modified = $value['videoModified'];
        $vid->duration = $value['duration'];
        $vid->description = $value['description'];
        $vid->type = $value['type'];
        $vid->image_url = Video::getImageFromFilename($value['filename']);
        $obj->videos[] = $vid;
    }
}
$obj->totalVideosResponse = count($obj->videos);
$json = json_encode($obj);
file_put_contents($cachedFile, $json);
echo $json;
