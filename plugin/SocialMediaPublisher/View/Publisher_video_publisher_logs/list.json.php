<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_video_publisher_logs.php';
header('Content-Type: application/json');

$videos_id = getVideos_id();

if(empty($videos_id) && !User::isAdmin()){
    forbiddenPage('Admin only');
}

if(!Video::canEdit($videos_id)){
    forbiddenPage('You cannot edit this video');
}

if(empty($videos_id)){
    $rows = Publisher_video_publisher_logs::getAll();
    $total = Publisher_video_publisher_logs::getTotal();
}else{
    $rows = Publisher_video_publisher_logs::getAllFromVideosId($videos_id);
    $total = Publisher_video_publisher_logs::getTotalFromVideosId($videos_id);
}

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>