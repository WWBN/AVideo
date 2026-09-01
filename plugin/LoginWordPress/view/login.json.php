<?php
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/../../../videos/configuration.php';

forbidIfIsUntrustedRequest('LoginWordPress');

$obj = AVideoPlugin::getObjectDataIfEnabled('LoginWordPress');

// Do not log the caller off before authenticating (see objects/login.json.php,
// same intentional omission) - LoginWordPress::login() replaces the session
// itself on success, and an unauthenticated caller must not be able to
// destroy a victim's existing session by simply posting bad credentials here.
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
