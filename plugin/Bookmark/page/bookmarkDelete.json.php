<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Bookmark/Objects/BookmarkTable.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if(empty($_POST['id'])){
    $obj->msg = "ID can not be empty";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new BookmarkTable($id);

if(!User::isAdmin() && !Video::canEdit($row->getVideos_id())){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$obj->error = !$row->delete();
die(json_encode($obj));
?>