<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/BTCPayments/Objects/Btc_payments.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('BTCPayments');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$id = intval($_POST['id']);
$row = new Btc_payments($id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>