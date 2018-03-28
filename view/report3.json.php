<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

$from = date("Y-m-d 00:00:00", strtotime($_POST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));

// list all channels
$users = User::getAllUsers();

$rows = array();
foreach ($users as $key => $value) {
    // list all videos on that channel
    $identification = User::getNameIdentificationById($value['id']);
    $thumbs = Video::getTotalVideosThumbsUpFromUser($value['id'], $from, $to);
    $item = array(
        'thumbsUp'=>$thumbs['thumbsUp'],
        'thumbsDown'=>$thumbs['thumbsDown'],
        'channel'=>"<a href='{$global['webSiteRootURL']}channel/{$value['id']}'>{$identification}</a>"

    );
    $row[] = $item;
}

$obj = new stdClass();

$obj->data = $row;

echo json_encode($obj);

