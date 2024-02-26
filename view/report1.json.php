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
$from = date("Y-m-d 00:00:00", strtotime($_POST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_POST['dateTo']));
_error_log("report1: from=$from to=$to");
if ($config->getAuthCanViewChart() == 0) {
    // list all channels
    if (User::isAdmin()) {
        _error_log("report1: line=".__LINE__);
        $users = Channel::getChannels();
    } elseif (User::isLogged()) {
        _error_log("report1: line=".__LINE__);
        $users = [['id'=> User::getId()]];
    } else {
        _error_log("report1: line=".__LINE__);
        $users = [];
    }
} elseif ($config->getAuthCanViewChart() == 1) {
    if ((!empty($_SESSION['user']['canViewChart']))||(User::isAdmin())) {
        _error_log("report1: line=".__LINE__);
        $users = Channel::getChannels();
    }
}

_error_log("report1: users=".count($users));
$rows = [];
foreach ($users as $key => $value) {
    // list all videos on that channel
    $videos = Video::getAllVideosLight("a", $value['id']);
    $identification = 'users_id='.$value['id'];
    //$identification = User::getNameIdentificationById($value['id']);
    $views = 0;
    _error_log("report1: count={$key} users_id=".$value['id'].' total videos='.count($videos));
    foreach ($videos as $key2 => $value2) {
        $views+=VideoStatistic::getStatisticTotalViews($value2['id'], false, $from, $to);
    }
    if (empty($views)) {
        continue;
    }
    $item = [
        'views'=>$views,
        'channel'=>"<a href='".User::getChannelLink($value['id'])."'>{$identification}</a>",

    ];
    $rows[] = $item;
}

$obj = new stdClass();

$obj->data = $rows;

_error_log("report1: final count=".count($rows));
echo json_encode($obj);
