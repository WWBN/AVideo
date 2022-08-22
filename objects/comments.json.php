<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
//setDefaultSort('id', 'DESC');
if(empty($_REQUEST['id'])){
    if(empty($_POST['sort'])){
       $_POST['sort'] = array();
       $_POST['sort']['pin'] = 'ASC';
       $_POST['sort']['id'] = 'ASC';
    }
    $comments = Comment::getAllComments($_REQUEST['video_id'], @$_REQUEST['comments_id']);
    $total = Comment::getTotalComments($_REQUEST['video_id'], @$_REQUEST['comments_id']);
}else{
    $comment = Comment::getComment($_REQUEST['id']);
    if(!empty($comment)){
        $comments = array($comment);
        $total = 1;
    }else{
        $comments = array();
        $total = 0;
    }
}

foreach ($comments as $key => $value) {
    $comments[$key] = Comment::addExtraInfo2($value);
}

$obj = new stdClass();
$obj->current = getCurrentPage();
$obj->rowCount = getRowCount();
$obj->total = $total;
$obj->rows = $comments;

echo json_encode($obj);
