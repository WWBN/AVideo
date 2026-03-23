<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaignsVideos.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
	forbiddenPage('You must be Admin');
}

$rows = VastCampaignsVideos::getAllFromCampaign(intval(@$_POST['id']), true);
?>
{"data": <?php echo json_encode($rows); ?>}
