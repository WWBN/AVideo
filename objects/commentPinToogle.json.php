<?php
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->comments_id = intval($_REQUEST['comments_id']);

if (empty($obj->comments_id)) {
    forbiddenPage('comments_id is required');
}

require_once 'comment.php';
$objC = new Comment('', '', $obj->comments_id);
$obj->videos_id = $objC->getVideos_id();

if (!Video::canEdit($obj->videos_id)) {
    forbiddenPage('Cannot edit videos');
}

$obj->old_pin_value = $objC->getPin();
$obj->new_pin_value = intval(!$obj->old_pin_value);
$objC->setPin($obj->new_pin_value);
$obj->save = $objC->save();
if (!empty($obj->save)) {
    $obj->error = false;
    $obj->comment = Comment::getComment($obj->comments_id);
    $obj->comment = Comment::addExtraInfo2($obj->comment);
    if($obj->new_pin_value){
        $obj->msg = __("Your comment is pinned");
    }else{
        $obj->msg = __("Your comment is unpinned");
    }
} else {
    $obj->msg = __("Your pin has NOT been saved!");
}
die(json_encode($obj));
