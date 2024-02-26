<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/comment.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->id = intval(@$_REQUEST['id']);
$obj->status = false;

if(empty($obj->id)){
    $obj->id = intval(@$_REQUEST['comments_id']);
}

if (empty($obj->id)) {
    $obj->msg = __("ID can not be empty");
    die(_json_encode($obj));
}

$objC = new Comment("", 0, $obj->id);
$obj->videos_id = $objC->getVideos_id();
$obj->status = $objC->delete();

if(!empty($obj->status)){
    $obj->error = false;
    //$obj->comments = Comment::getAllComments($obj->videos_id);
    //$obj->comments = Comment::addExtraInfo($obj->comments);

}

die(_json_encode($obj));
