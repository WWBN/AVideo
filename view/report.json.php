<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

AVideoPlugin::getDataObject('VideosStatistics');

session_write_close();

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->configAuthCanViewChart = $config->getAuthCanViewChart();
$obj->performance = new stdClass();
$obj->performance->start = microtime(true);

if(empty($_REQUEST['users_id'])){
    $obj->users_id = User::getId();
}else{
    $obj->users_id = intval($_REQUEST['users_id']);
}

if(empty($obj->users_id)){
    $obj->msg = 'You MUST Specify a user';
    die(_json_encode($obj));
}

$user = new User($obj->users_id);

if(empty($user->getUser())){
    $obj->msg = 'Invalid user';
    die(_json_encode($obj));
}

$obj->users_id_statistics = $obj->users_id;
if(User::isAdmin() && !empty($_REQUEST['isAdminPanel'])){
    $obj->users_id_statistics = 0; // show all results
}else if(User::getId() !== $obj->users_id_statistics){
    $obj->msg = 'Invalid user';
    die(_json_encode($obj));
}

$cacheName = 'statisticsReport_'.$obj->users_id_statistics;

$cache = ObjectYPT::getCache($cacheName, 300); // 5 min cache
if(!empty($cache)){
    if(empty($cache->performance)){
        $cache->performance = new stdClass();
    }
    if(empty($cache->performance->cache)){
        $cache->performance->cache = new stdClass();
    }
    $cache->performance->cache->time = time();
    $cache->performance->cache->date = date('Y-m-d H:i:s');
    $cache->performance->cache->cache_duration = microtime(true) - $obj->start;
    $cache->performance->cache->human = humanTimingAgo($cache->performance->time, 2);
    die(_json_encode($cache));
}

$obj->can_upload = $user->getCanUpload();
$obj->can_view_charts = $user->getCanViewChart();

if (!$user->getIsAdmin() && empty($obj->can_view_charts)) {
    if ($obj->configAuthCanViewChart == 0 && !$obj->can_upload) {
        $obj->msg = 'Only uploaders have charts';
        die(_json_encode($obj));
    }
    if ($obj->configAuthCanViewChart == 1) {
        $obj->msg = 'This user is not selected to display charts';
        die(_json_encode($obj));
    }
}

$obj->error = false;
$obj->totalVideos = VideosStatistics::getTotalVideos($obj->users_id_statistics);
$obj->totalSubscriptions = VideosStatistics::getTotalSubscriptions($obj->users_id_statistics);
$obj->totalComents = VideosStatistics::getTotalComments($obj->users_id_statistics);
$obj->totalVideosViews = VideosStatistics::getTotalVideosViews($obj->users_id_statistics);
$obj->totalDurationVideos = intval(VideosStatistics::getTotalDuration($obj->users_id_statistics) / 60);
$obj->totalLikes = VideosStatistics::getTotalLikes($obj->users_id_statistics);
$obj->totalDislikes = VideosStatistics::getTotalDislikes($obj->users_id_statistics);

$obj->today = VideosStatistics::getMostViewedVideosFromLastDays($obj->users_id_statistics, 1);
$obj->last7Days = VideosStatistics::getMostViewedVideosFromLastDays($obj->users_id_statistics, 7);
$obj->last15Days = VideosStatistics::getMostViewedVideosFromLastDays($obj->users_id_statistics, 15);
$obj->last30Days = VideosStatistics::getMostViewedVideosFromLastDays($obj->users_id_statistics, 30);
$obj->last90Days = VideosStatistics::getMostViewedVideosFromLastDays($obj->users_id_statistics, 90);


$obj->performance->end = microtime(true);
$obj->performance->time = time();
$obj->performance->date = date('Y-m-d H:i:s');
$obj->performance->duration = $obj->end - $obj->start;


ObjectYPT::setCache($cacheName, $obj);

echo _json_encode($obj);
