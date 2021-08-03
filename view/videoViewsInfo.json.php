<?php
require_once '../videos/configuration.php';
header('Content-Type: application/json');

$videos_id = intval(@$_REQUEST['videos_id']);

if (empty($videos_id)) {
    forbiddenPage("Videos ID is required");
}

if (!Video::canEdit($videos_id)) {
    forbiddenPage("You cannot see this info");
}

$rows = VideoStatistic::getAllFromVideos_id($videos_id);
$total = VideoStatistic::getTotalFromVideos_id($videos_id);
$totalPages = ceil($total / $_REQUEST['rowCount']);

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}