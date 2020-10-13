<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LoginControl/Objects/Users_login_history.php';
header('Content-Type: application/json');
if(!User::isAdmin()){
    die('{"data": []}');
}
$rows = Users_login_history::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}