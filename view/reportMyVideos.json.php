<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
_session_write_close();
require_once __DIR__ . '/../objects/reportDateRange.php';
[$from, $to] = reportRequestDateRange($_POST);
$users_id = 0;
if ($config->getAuthCanViewChart() == 0) {
    // list all channels
    if (User::isAdmin()) {
        if(empty($_REQUEST['users_id'])){
            $users_id = 'all';
        }else{
            $users_id = $_REQUEST['users_id'];
        }
    } elseif (User::isLogged()) {
        $users_id = User::getId();
    }
} elseif ($config->getAuthCanViewChart() == 1) {
    if ((!empty($_SESSION['user']['canViewChart']))||(User::isAdmin())) {
        if(empty($_REQUEST['users_id'])){
            $users_id = 'all';
        }else{
            $users_id = $_REQUEST['users_id'];
        }
    }
}

$obj = new stdClass();


$obj->data = [];


if(empty($users_id)){
    die(json_encode($obj));
}

if($users_id === 'all'){
    $users_id = 0;
}

try {
    $obj->data = VideoStatistic::getStatisticTotalViewsAndSecondsWatchingFromUser($users_id, $from, $to);

} catch (Throwable $e) {
    _error_log('Analytics query failed: ' . $e->getMessage());
    http_response_code(500);
    $obj->error = true;
    $obj->msg = __('Unable to load this report. Please try again.');
}

echo json_encode($obj);
