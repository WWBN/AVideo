<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/LoginControl/Objects/logincontrol_history.php';
header('Content-Type: application/json');
if(!User::isAdmin() || empty($_REQUEST['users_id'])){
    $_REQUEST['users_id'] = User::getId();
}else if(!empty ($_REQUEST['users_id'])){
    $_REQUEST['users_id'] = intval($_REQUEST['users_id']);
}

if(empty($_REQUEST['users_id'])){
    die('{"data": []}');
}
$rows = logincontrol_history::getLastLogins($_REQUEST['users_id'], 100);
?>
{"data": <?php echo json_encode($rows); ?>}