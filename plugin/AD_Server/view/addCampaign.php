<?php
header('Content-Type: application/json');
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new VastCampaigns(@$_POST['campId']);
$o->setName($_POST['name']);
$o->setType("Contract");
$o->setStatus($_POST['status']);
$o->setStart_date($_POST['start_date']);
$o->setEnd_date($_POST['end_date']);
$o->setPricing_model("CPM");
$o->setPriority(10);
$o->setUsers_id(User::getId());
$o->setCpm_max_prints($_POST['maxPrints']);
$o->setVisibility('listed');

if($o->save()){
    $obj->error = false;
}
echo json_encode($obj);
