<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';
header('Content-Type: application/json');

$rows = Users_connections::getAll();
$total = Users_connections::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>