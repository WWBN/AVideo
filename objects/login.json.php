<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$user = new User(0, $_POST['user'], $_POST['pass']);
$user->login();
$object = new stdClass();
$object->isLogged = User::isLogged();
echo json_encode($object);