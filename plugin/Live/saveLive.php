<?php
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$obj = new stdClass();
$obj->error = true;
if(!User::canUpload()){
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

$l = new LiveTransmition(0);
$l->loadByUser(User::getId());
$l->setTitle($_POST['title']);
$l->setDescription($_POST['description']);
$l->setKey($_POST['key']);
$l->setCategories_id(1);
$l->setUsers_id(User::getId());
echo '{"status":"'.$l->save().'"}';