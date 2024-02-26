<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LiveLinks/Objects/LiveLinksTable.php';
header('Content-Type: application/json');

if(!User::isLogged()){
    die('{"data": []}');
}

$users_id = 0;
if(!User::isAdmin()){
    $users_id = User::getId();
}

$rows = LiveLinksTable::getAll($users_id);
?>
{"data": <?php echo json_encode($rows); ?>}