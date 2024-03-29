<?php

require_once '../../videos/configuration.php';
require_once './Objects/LiveTransmition.php';
require_once '../../objects/user.php';
$obj = new stdClass();
$obj->error = true;
if (!User::canStream()) {
    $obj->msg = __('Permission denied');
    die(json_encode($obj));
}

$categories_id = intval(@$_POST['categories_id']);
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
$l->loadByUser(User::getId());
$l->setTitle($_POST['title']);
$l->setDescription($_POST['description']);
$l->setPassword($_POST['password']);
$l->setKey($_POST['key']);
$l->setCategories_id($categories_id);
$l->setPublicAutomatic();
$l->setSaveTransmitionAutomatic();
$l->setUsers_id($users_id);
$id = $l->save();
$l = new LiveTransmition($id);
$l->deleteGroupsTrasmition();
if (!empty($_POST['userGroups'])) {
    foreach ($_POST['userGroups'] as $value) {
        $l->insertGroup($value);
    }
}
echo '{"status":"'.$id.'"}';
