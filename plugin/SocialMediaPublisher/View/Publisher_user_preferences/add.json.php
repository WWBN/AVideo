<?php
header('Content-Type: application/json');
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_user_preferences.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
                                                
if(!User::isLogged()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$row = SocialMediaPublisher::getOrCreateSocialMedia($_POST['provider']);
if(empty($row)){
    $obj->msg = "error on get provider {$_POST['provider']}";
    die(json_encode($obj));
}

$o = new Publisher_user_preferences(@$_POST['id']);
$o->setPublisher_social_medias_id($row['id']);
$o->setPreferred_profile($_POST['name']);
$o->setUsers_id(User::getId());
$o->setJson($_POST['json']);

if($id = $o->save()){
    $obj->error = false;
}

echo json_encode($obj);
