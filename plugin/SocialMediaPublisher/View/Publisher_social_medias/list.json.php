<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_social_medias.php';
header('Content-Type: application/json');

$rows = Publisher_social_medias::getAll();
$total = Publisher_social_medias::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>