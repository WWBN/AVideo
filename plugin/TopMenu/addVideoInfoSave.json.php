<?php

header('Content-Type: application/json');
require_once '../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->videos_id = 0;

$obj->videos_id = intval($_REQUEST['videos_id']);
if (empty($obj->videos_id)) {
    $obj->msg = 'You cannot edit videos_id ' . $obj->videos_id;
    die(json_encode($obj));
}

$obj->menu_item_id = intval($_REQUEST['menu_item_id']);
if (empty($obj->menu_item_id)) {
    $obj->msg = 'menu_item_id is empty';
    die(json_encode($obj));
}

$obj->url = $_REQUEST['url'];
if (!IsValidURL($obj->url)) {
    $obj->url = '';
}

if (!Video::canEdit($obj->videos_id )) {
    $obj->msg = 'You cannot edit videos_id ' . $obj->videos_id;
    die(json_encode($obj));
}

$id = TopMenu::setVideoMenuURL($obj->videos_id, $obj->menu_item_id, $obj->url);

$obj->error = empty($id);

echo json_encode($obj);
