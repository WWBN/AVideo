<?php
require_once 'comment.php';
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
    //$comments[$key]['comment'] = " <div class=\"commenterName\"><strong>{$name}</strong><div class=\"date sub-text\">{$value['created']}</div></div><div class=\"commentText\">". nl2br($value['comment'])."</div>";
    $comments[$key]['comment'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong><a href="'.User::getChannelLink($value['users_id']).'/">'.$name.'</a></strong> <small>'.humanTiming(strtotime($value['created'])).'</small></div>'. fixCommentText(nl2br(textToLink($value['comment']))).'</div>';
    $comments[$key]['total_replies'] = Comment::getTotalComments($_GET['video_id'], $comments[$key]['id']);
    $comments[$key]['video'] = Video::getVideo($comments[$key]['videos_id']);
    $comments[$key]['poster'] = Video::getImageFromFilename($comments[$key]['video']['filename']);
    $comments[$key]['userCanAdminComment'] = Comment::userCanAdminComment($comments[$key]['id']);
}

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($comments).'}';
