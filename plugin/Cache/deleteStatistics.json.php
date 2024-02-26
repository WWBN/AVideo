<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->result = "";

if (!User::isAdmin()) {
    $obj->msg = "You can't do this";
    die(json_encode($obj));
}

require_once $global['systemRootPath'] . 'objects/video_statistic.php';

$objC = AVideoPlugin::getDataObject('Cache');

$days = $objC->deleteStatisticsDaysOld;
if (empty($days)) {
    $days = 180;
}
$obj->before = VideoStatistic::getTotalStatisticsRecords();
$obj->result = VideoStatistic::deleteOldStatistics($days);
$obj->after = VideoStatistic::getTotalStatisticsRecords();
$obj->error = empty($obj->result);

$obj->msg = "you had ". number_format($obj->before, 0)." statistics records and removed ". number_format($obj->before-$obj->after, 0)." now you have ". number_format($obj->after, 0);

echo json_encode($obj);
