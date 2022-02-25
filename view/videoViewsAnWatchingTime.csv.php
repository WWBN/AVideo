<?php
require_once '../videos/configuration.php';
header('Content-Type: application/json');

session_write_close();
$from = date("Y-m-d 00:00:00", strtotime($_REQUEST['dateFrom']));
$to = date('Y-m-d 23:59:59', strtotime($_REQUEST['dateTo']));
$fromDate = date("Y-m-d", strtotime($_REQUEST['dateFrom']));
$toDate = date('Y-m-d', strtotime($_REQUEST['dateTo']));
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


$obj->data = array();


if(empty($users_id)){
    die(json_encode($obj));
}

if($users_id === 'all'){
    $users_id = 0;
}

$obj->data = VideoStatistic::getStatisticTotalViewsAndSecondsWatchingFromUser($users_id, $from, $to);

$rows = array();
foreach ($obj->data as $value) {
    $rows[] = array(
        $value['videos_id'],
        $value['title'],
        $value['type'],
        $value['total_views'],
        intval($value['seconds_watching_video'])
     );
}

$filename = "{$users_id}_{$fromDate}_{$toDate}";
//var_dump($rows);exit;
$output = fopen("php://output", 'w') or die("Can't open php://output");
$identification = 'All Users';
if(!empty($users_id)){
    $identification = User::getNameIdentificationById($users_id);
}
fputcsv($output, array('From', $fromDate, 'To', $toDate, 'User', "[{$users_id}] {$identification}"));
$fields = ['videos_id', 'title', 'type', 'total views', 'seconds watching video'];
fputcsv($output, $fields);
foreach ($rows as $row) {
    fputcsv($output, $row);
}
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename={$filename}.csv");
fclose($output) or die("Can't close php://output");
