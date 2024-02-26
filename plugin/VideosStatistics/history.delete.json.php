<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;

$plugin = AVideoPlugin::loadPluginIfEnabled('VideosStatistics');

if(!User::isLogged()){
   forbiddenPage('You Must login');
}
$deleted = Statistics::deleteVideoStatistics(User::getId(), $_REQUEST['id']);
$obj->error = !$deleted;
die(json_encode($obj));
?>