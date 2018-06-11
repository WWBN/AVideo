<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

$obj = new stdClass();
if(empty($_POST['captcha'])){
   $obj->error = __("The captcha is empty");
   die(json_encode($obj));
}
require_once $global['systemRootPath'] . 'objects/captcha.php';
$valid = Captcha::validation($_POST['captcha']);
if(!$valid){
   $obj->error = __("The captcha is wrong");
   die(json_encode($obj));
}
// check if user already exists
$userCheck = new User(0, $_POST['user'], false);

if (!empty($userCheck->getBdId())) {
    $obj->error = __("User already exists");
    die(json_encode($obj));
}

if (empty($_POST['user']) || empty($_POST['pass']) || empty($_POST['email']) || empty($_POST['name'])) {
    $obj->error = __("You must fill all fields");
    die(json_encode($obj));
}
$user = new User(0);
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);

$user->setCanUpload($config->getAuthCanUploadVideos());

echo '{"status":"'.$user->save().'"}';
