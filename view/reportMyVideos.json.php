<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
session_write_close();
$from = date("Y-m-d 00:00:00", strtotime($_POST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));
$users_id = 0;
if ($config->getAuthCanViewChart() == 0) {
    // list all channels
    if (User::isAdmin()) {
        $users_id = 'all';
    } elseif (User::isLogged()) {
        $users_id = User::getId();
    } 
} elseif ($config->getAuthCanViewChart() == 1) {
    if ((!empty($_SESSION['user']['canViewChart']))||(User::isAdmin())) {
        $users_id = 'all';
    }
}

$obj = new stdClass();


$obj->data = array();


if(empty($users_id)){
    die(json_encode($obj));
}

if($users_id === 'all'){
    $users_id = 0;
}

$obj->data = VideoStatistic::getStatisticTotalViewsAndSecondsWatchingFromUser($users_id, $from, $to);

echo json_encode($obj);
