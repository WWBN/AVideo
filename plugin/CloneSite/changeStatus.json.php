<?php
header('Content-Type: application/json');
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if(!User::isAdmin()){
    $obj->msg = "You cant do this";
    die(json_encode($obj));
}

$o = new Clones($_POST['id']);

if($o->toogleStatus()){
    $obj->error = false;
}
echo json_encode($obj);
