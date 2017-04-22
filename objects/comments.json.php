<?php
require_once 'comment.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
header('Content-Type: application/json');
$comments = Comment::getAllComments($_GET['video_id']);
$total = Comment::getTotalComments($_GET['video_id']);
foreach ($comments as $key => $value) {
    $name = empty($value['name'])?$value['user']:$value['name'];
    //$comments[$key]['comment'] = " <div class=\"commenterName\"><strong>{$name}</strong><div class=\"date sub-text\">{$value['created']}</div></div><div class=\"commentText\">". nl2br($value['comment'])."</div>";
    $comments[$key]['comment'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong>'.$name.'</strong> <small>'.humanTiming(strtotime($value['created'])).'</small></div>'. nl2br(textToLink($value['comment'])).'</div>';
}


echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($comments).'}';