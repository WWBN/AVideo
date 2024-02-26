<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AI/Objects/Ai_metatags_responses.php';
header('Content-Type: application/json');

$rows = Ai_metatags_responses::getAll();
$total = Ai_metatags_responses::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>