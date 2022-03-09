<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Users_affiliations.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->id = intval($_POST['id']);

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if (empty($obj->id)) {
    forbiddenPage('Invalid ID');
}

if(!Users_affiliations::canEditAffiliation($obj->id)){
    forbiddenPage();
}

$row = new Users_affiliations($obj->id);
$obj->error = !$row->delete();
die(json_encode($obj));
?>