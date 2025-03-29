<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$obj = new stdClass();
$obj->error = true;
if (!User::canStream()) {
    forbiddenPage('Permission denied');
}
$objLive = AVideoPlugin::getDataObject("Live");
$categories_id = intval(@$_REQUEST['categories_id']);
if (empty($categories_id)) {
    $categories_id = 1;
}

$users_id = User::getId();

if (User::isAdmin()) {
    if (!empty($_REQUEST['users_id'])) {
        $users_id = $_REQUEST['users_id'];
    }
}

$l = new LiveTransmition(0);
$l->loadByUser($users_id);
$l->setTitle($_REQUEST['title']);
$l->setDescription($_REQUEST['description']);
$l->setPassword($_REQUEST['password']);
$l->setKey($_REQUEST['key']);
$l->setCategories_id($categories_id);
$l->setIsRebroadcast($_REQUEST['isRebroadcast']);
$l->setPublicAutomatic();
$l->setSaveTransmitionAutomatic();
$l->setUsers_id($users_id);
$id = $l->save();
if(empty($id)){
    forbiddenPage('Error on save');
}

LiveTransmition::getFromDb($id, true);

$resp = array('error'=>false, 'msg'=>'Saved', 'userGroups'=>array(), 'id'=>$id, 'LiveTransmition'=>LiveTransmition::getFromDb($id, true));

$l = new LiveTransmition($id);
$l->deleteGroupsTrasmition();

if (!empty($_REQUEST['userGroups']) && !$objLive->hideUserGroups) {
    _error_log("LiveTransmition::save key={$_REQUEST['key']} \$users_id={$users_id} User::getId=".User::getId().' IP='.getRealIpAddr().' userGroups='.json_encode($_REQUEST['userGroups']).' saving usergroups '.json_encode(debug_backtrace()));
    foreach ($_REQUEST['userGroups'] as $value) {
        $resp['userGroups'][] = $value;
        $l->insertGroup($value);
    }
}
echo json_encode($resp);
