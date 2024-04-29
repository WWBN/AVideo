<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_social_medias.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
                                                
if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Publisher_social_medias(@$_POST['id']);
$o->setName($_POST['name']);
$o->setApi_details($_POST['api_details']);
$o->setStatus('a');

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
