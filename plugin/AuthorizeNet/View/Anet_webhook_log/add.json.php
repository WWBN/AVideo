<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/Objects/Anet_webhook_log.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('AuthorizeNet');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Anet_webhook_log(@$_POST['id']);
$o->setUniq_key($_POST['uniq_key']);
$o->setEvent_type($_POST['event_type']);
$o->setTrans_id($_POST['trans_id']);
$o->setPayload_json($_POST['payload_json']);
$o->setProcessed($_POST['processed']);
$o->setError_text($_POST['error_text']);
$o->setStatus($_POST['status']);
$o->setCreated_php_time($_POST['created_php_time']);
$o->setModified_php_time($_POST['modified_php_time']);
$o->setUsers_id($_POST['users_id']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
