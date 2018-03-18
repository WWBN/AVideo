<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$obj = new stdClass();

$obj->title = $config->getWebSiteTitle();
$obj->url = $global['webSiteRootURL'];
$obj->language = $config->getLanguage();
$obj->version = $config->getVersion();
$obj->videos = array();

//$_GET['modified'] = "2018-03-13 15:46:57";
$videos = Video::getAllVideos();
        
foreach ($videos as $key => $value) {
    $vid =  new stdClass();
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

header('Content-Type: application/json');
echo json_encode($obj);