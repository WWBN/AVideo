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
foreach ($rows as $key => $value) {
    $rows[$key]['image'] = LiveLinks::getImagesPaths($value['id']);
    $rows[$key]['identification'] = User::getNameIdentificationById($value['users_id']);
}

$data = new stdClass();
$data->data = $rows;
echo json_encode($data);
?>