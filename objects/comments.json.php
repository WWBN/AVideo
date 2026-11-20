<?php
global $global, $config;
require_once __DIR__.'/../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/comment.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
setRowCount(10);

//setDefaultSort('id', 'DESC');
if(empty($_REQUEST['id'])){
    if(empty($_POST['sort'])){
       $_POST['sort'] = [];
       $_POST['sort']['pin'] = 'DESC';
       //$_POST['sort']['comments_id_pai'] = 'IS NULL DESC';
       //$_POST['sort']['comments_id_pai'] = 'DESC';
       $_POST['sort']['id'] = 'DESC';
    }
    forbiddenPageIfCannotWatchVideo(@$_REQUEST['video_id']);
    // Without video_id, getAllComments() returns the replies of any parent comment, so check the parent's video
    // (comments manager loads replies with video_id=0; comment moderators and users who can edit the video keep access)
    if (empty($_REQUEST['video_id']) && !empty($_REQUEST['comments_id']) && !Permissions::canAdminComment()) {
        $parentComment = Comment::getComment($_REQUEST['comments_id']);
        if (!empty($parentComment) && !Video::canEdit($parentComment['videos_id'])) {
            forbiddenPageIfCannotWatchVideo($parentComment['videos_id']);
        }
    }
    $comments = Comment::getAllComments(@$_REQUEST['video_id'], @$_REQUEST['comments_id'], 0, true);
    $total = Comment::getTotalComments(@$_REQUEST['video_id'], @$_REQUEST['comments_id']);
}else{
    $comment = Comment::getComment($_REQUEST['id']);
    if(!empty($comment)){
        forbiddenPageIfCannotWatchVideo($comment['videos_id']);
        $comments = [$comment];
        $total = 1;
    }else{
        $comments = [];
        $total = 0;
    }
}

$comments = Comment::addExtraInfo2InRows($comments);

$obj = new stdClass();
$obj->current = getCurrentPage();
$obj->rowCount = getRowCount();
$obj->total = $total;
$obj->rows = $comments;

echo json_encode($obj);
