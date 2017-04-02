<?php

require_once 'video.php';
header('Content-Type: application/json');
$videos = Video::getAllVideos("");
$total = Video::getTotalVideos("");

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($videos).'}';