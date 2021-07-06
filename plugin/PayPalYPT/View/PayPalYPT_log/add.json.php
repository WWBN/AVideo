<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/PayPalYPT/Objects/PayPalYPT_log.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('PayPalYPT');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new PayPalYPT_log(@$_POST['id']);
$o->setAgreement_id($_POST['agreement_id']);
$o->setUsers_id($_POST['users_id']);
$o->setJson($_POST['json']);
$o->setRecurring_payment_id($_POST['recurring_payment_id']);
$o->setValue($_POST['value']);
$o->setToken($_POST['token']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
