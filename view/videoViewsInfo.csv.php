<?php
require_once '../videos/configuration.php';
header('Content-Type: application/json');

if (!empty($_REQUEST['hash'])) {
    $string = decryptString($_REQUEST['hash']);
    $obj = json_decode($string);
    $videos_id = intval($obj->videos_id);
} else {
    $videos_id = intval(@$_REQUEST['videos_id']);
    if (!Video::canEdit($videos_id)) {
        forbiddenPage("You cannot see this info");
    }
}
if (empty($videos_id)) {
    forbiddenPage("Videos ID is required");
}

$rowsCount = getRowCount();
$video = new Video('', '', $videos_id);
$year = intval(@$_REQUEST['created_year']);
$month = intval(@$_REQUEST['created_month']);
$filename = "{$year}{$month}_{$videos_id}_".$video->getClean_title();
$rows = VideoStatistic::getAllFromVideos_id($videos_id);
//var_dump($rows);exit;
$output = fopen("php://output", 'w') or die("Can't open php://output");
$fields = ['when', 'ip', 'users', 'location_name', 'seconds_watching_video'];
fputcsv($output, $fields);
foreach ($rows as $row) {
    $statistic = [];
    foreach ($fields as $value) {
        $statistic[$value] = $row[$value];
    }
    //var_dump($statistic);exit;
    fputcsv($output, $statistic);
}
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename={$filename}.csv");
fclose($output) or die("Can't close php://output");
