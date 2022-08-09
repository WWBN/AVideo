<?php
//header('Content-Type: application/json');
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
                                                
if(!User::isAdmin() && !Video::canEdit($_POST['videos_id'])){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new BookmarkTable(@$_POST['id']);
$o->setName($_POST['name']);
$o->setTimeInSeconds($_POST['timeInSeconds']);
$o->setVideos_id($_POST['videos_id']);

if($id = $o->save()){
    $obj->error = false;
}


echo json_encode($obj);
