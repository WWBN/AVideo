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
$comments = Comment::getAllComments($_GET['video_id'], @$_POST['comments_id']);
$total = Comment::getTotalComments($_GET['video_id'], @$_POST['comments_id']);

function fixCommentText($subject){
    $search = array('\n');
    $replace = array("<br/>");
    return stripslashes(str_replace($search, $replace, $subject));
}

foreach ($comments as $key => $value) {
    $name = User::getNameIdentificationById($value['users_id']);
    $comments[$key]['comment'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="User Photo" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong><a href="'.User::getChannelLink($value['users_id']).'">'.$name.'</a></strong> <small>'.humanTiming(strtotime($value['created'])).'</small></div>'. fixCommentText(textToLink($value['commentHTML'])).'</div>';
    $comments[$key]['total_replies'] = Comment::getTotalComments($_GET['video_id'], $comments[$key]['id']);
    $comments[$key]['video'] = Video::getVideo($comments[$key]['videos_id']);
    unset($comments[$key]['video']['description']);
    $comments[$key]['poster'] = Video::getImageFromFilename($comments[$key]['video']['filename']);
    $comments[$key]['userCanAdminComment'] = Comment::userCanAdminComment($comments[$key]['id']);
    $comments[$key]['userCanEditComment'] = Comment::userCanEditComment($comments[$key]['id']);
}

echo '{  "current": '. getCurrentPage().',"rowCount": '. getRowCount().', "total": '.$total.', "rows":'. json_encode($comments).'}';
