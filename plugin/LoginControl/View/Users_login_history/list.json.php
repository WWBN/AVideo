<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LoginControl/Objects/logincontrol_history.php';
header('Content-Type: application/json');
if(!User::isAdmin()){
    die('{"data": []}');
}
$rows = logincontrol_history::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}