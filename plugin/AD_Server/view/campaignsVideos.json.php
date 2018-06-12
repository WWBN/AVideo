<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';
header('Content-Type: application/json');

$rows = VastCampaignsVideos::getAllFromCampaign(@$_POST['id'], true);
?>
{"data": <?php echo json_encode($rows); ?>}