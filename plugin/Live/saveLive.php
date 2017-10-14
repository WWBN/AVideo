<?php
require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$obj = new stdClass();
$obj->error = true;
if(!User::canStream()){
    $obj->msg = __("Permition denied");
    die(json_encode($obj));
}

$l = new LiveTransmition(0);
$l->loadByUser(User::getId());
$l->setTitle($_POST['title']);
$l->setDescription($_POST['description']);
$l->setKey($_POST['key']);
$l->setCategories_id(1);
$l->setPublic((empty($_POST['listed'])|| $_POST['listed']==='false')?0:1);
$l->setUsers_id(User::getId());
$id = $l->save();
$l = new LiveTransmition($id);
$l->deleteGroupsTrasmition();
if(!empty($_POST['userGroups'])){
    foreach ($_POST['userGroups'] as $value) {
        $l->insertGroup($value);    
    }
}
echo '{"status":"'.$id.'"}';
