<?php

header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_affiliations.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->id = intval($_POST['id']);

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if (empty($obj->id)) {
    forbiddenPage('Invalid ID');
}

if(!Users_affiliations::canEditAffiliation($obj->id)){
    forbiddenPage();
}

$o = new Users_affiliations($obj->id);

if (empty($_REQUEST['confirm']) || $_REQUEST['confirm'] === 'false') {
    $date = 'NULL';
} else {
    $date = date('Y-m-d H:i:s');
}
if (User::isACompany()) {
    _error_log('Confirm affiliation is a company '.$date);
    $o->setCompany_agree_date($date);
} else {
    $o->setAffiliate_agree_date($date);
    _error_log('Confirm affiliation is NOT a company '.$date);
}
if ($id = $o->save()) {
    $obj->error = false;
}
echo json_encode($obj);
