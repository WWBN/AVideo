<?php

require_once 'video.php';
header('Content-Type: application/json');
$videos = Video::getAllVideos("", true);
$total = Video::getTotalVideos("", true);
foreach ($videos as $key => $value) {
    $name = empty($value['name'])?substr($value['user'], 0,5)."...":$value['name'];
    //$categories[$key]['comment'] = " <div class=\"commenterName\"><strong>{$name}</strong><div class=\"date sub-text\">{$value['created']}</div></div><div class=\"commentText\">". nl2br($value['comment'])."</div>";
    $videos[$key]['creator'] = '<div class="pull-left"><img src="'.User::getPhoto($value['users_id']).'" alt="" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails"><div class="commenterName"><strong>'.$name.'</strong> <small>'.humanTiming(strtotime($value['created'])).'</small></div></div>';
}

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($videos).'}';