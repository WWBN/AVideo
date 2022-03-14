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

$rows = VideoStatistic::getAllFromVideos_id($videos_id);
$total = VideoStatistic::getTotalFromVideos_id($videos_id);
$totalPages = ceil($total / $rowsCount);

?>
{"data": <?php echo json_encode($rows); ?>, "draw": <?php echo intval(@$_REQUEST['draw']); ?>, "recordsTotal":<?php echo $total; ?>, "recordsFiltered":<?php echo $total; ?>}