<?php
require_once 'comment.php';
header('Content-Type: application/json');
$categories = Comment::getAllComments($_GET['video_id']);
$total = Comment::getTotalComments($_GET['video_id']);

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($categories).'}';