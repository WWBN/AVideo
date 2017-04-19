<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
// check if user already exists
$userCheck = new User(0, $_POST['user'], false);
$obj = new stdClass();
if(!empty($userCheck->getBdId())){
    $obj->error = __("User already exists");
    die(json_encode($obj));
}

if(empty($_POST['user']) || empty($_POST['pass']) || empty($_POST['email']) || empty($_POST['name'])){
    $obj->error = __("You must fill all fields");
    die(json_encode($obj));
}
$user = new User(0);
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
echo '{"status":"'.$user->save().'"}';