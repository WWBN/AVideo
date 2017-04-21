<?php

require_once 'video.php';
header('Content-Type: application/json');
$videos = Video::getAllVideos("", true);
$total = Video::getTotalVideos("", true);

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($videos).'}';