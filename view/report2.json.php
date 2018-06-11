<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/comment.php';

$from = date("Y-m-d 00:00:00", strtotime($_POST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));

// list all channels
if(User::isAdmin()){
    $users = User::getAllUsers();
}else if(User::isLogged()){
    $users = array(array('id'=> User::getId()));
}else{
    $users = array();
}

$rows = array();
foreach ($users as $key => $value) {
    // list all videos on that channel
    $identification = User::getNameIdentificationById($value['id']);
    $thumbs = Comment::getTotalCommentsThumbsUpFromUser($value['id'], $from, $to);
    if(empty($thumbs['thumbsUp']) && empty($thumbs['thumbsDown'])){
        continue;
    }
    $item = array(
        'thumbsUp'=>$thumbs['thumbsUp'],
        'thumbsDown'=>$thumbs['thumbsDown'],
        'user'=>"<a href='".User::getChannelLink($value['id'])."'>{$identification}</a>"

    );
    $rows[] = $item;
}

$obj = new stdClass();

$obj->data = $rows;

echo json_encode($obj);
