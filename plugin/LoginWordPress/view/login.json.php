<?php
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../../videos/configuration.php';

$obj = AVideoPlugin::getObjectDataIfEnabled('LoginWordPress');

if(User::isLogged()){
    User::logoff();
}

$object = new stdClass();
$object->isLogged = false;
$object->isAdmin = false;
$object->canUpload = false;
$object->canComment = false;
$object->error = '';
$resp = LoginWordPress::login($_POST['WPuser'], $_POST['WPpass']);
if ($resp === User::USER_LOGGED) {
    $object->isLogged = User::isLogged();
    $object->isAdmin = User::isAdmin();
    $object->canUpload = User::canUpload();
    $object->canComment = User::canComment();
}
$object->isCaptchaNeed = User::isCaptchaNeed();
if($resp === User::CAPTCHA_ERROR){
    $object->error = __("Invalid Captcha");
}
if($resp === User::USER_NOT_FOUND){
    $object->error = __("User not found");
}
echo json_encode($object);
