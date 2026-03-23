<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
	forbiddenPage('You must be Admin');
}

$rows = VastCampaigns::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}
