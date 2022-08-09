<?php

$obj = new stdClass();
$obj->error = true;
if(empty($_GET['videos_id'])){
    die(json_encode($obj)); 
}
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'].'objects/video.php';
require_once $global['systemRootPath'].'plugin/Bookmark/Objects/BookmarkTable.php';
header('Content-Type: application/json');

$video = new Video("", "", $_GET['videos_id']);
$videos_id = $video->getId();
if(!empty($videos_id)){
    $obj->rows = BookmarkTable::getAllFromVideo($videos_id);
    $obj->error = false;
}

die(json_encode($obj)); 