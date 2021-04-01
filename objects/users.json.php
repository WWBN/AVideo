<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');

if (empty($_POST['current'])) {
    $_POST['current'] = 1;
}
if (empty($_REQUEST['rowCount'])) {
    $_REQUEST['rowCount'] = 10;
}
if(empty($_REQUEST['user_groups_id'])){
    $users = User::getAllUsers($advancedCustomUser->userCanChangeVideoOwner ? true : false, array('name', 'email', 'user', 'channelName', 'about'), @$_GET['status']);
    $total = User::getTotalUsers($advancedCustomUser->userCanChangeVideoOwner ? true : false, @$_GET['status']);
}else{
    $users = User::getAllUsersFromUsergroup($_REQUEST['user_groups_id'], $advancedCustomUser->userCanChangeVideoOwner ? true : false, array('name', 'email', 'user', 'channelName', 'about'), @$_GET['status']);
    $total = User::getTotalUsersFromUsergroup($_REQUEST['user_groups_id'], $advancedCustomUser->userCanChangeVideoOwner ? true : false, @$_GET['status']);
}
//echo examineJSONError($users);exit;
$json = json_encode($users);
if (json_last_error()) {
    _error_log("users.json error 1: " . print_r($users, true));
    $users = object_to_array($users);
    //echo examineJSONError($users);exit;
    array_walk_recursive($users, function(&$item) {
        if (is_string($item)) {
            $item = cleanString($item);
        }
    });
    $json = json_encode($users);
}
if (json_last_error()) {
    _error_log("users.json error 2 ");
    foreach ($users as $key => $value) {
        $users[$key]['about'] = "";
    }
    $json = json_encode($users);
}

echo '{  "current": ' . $_POST['current'] . ',"rowCount": ' . $_REQUEST['rowCount'] . ', "total": ' . $total . ', "rows":' . $json . '}';
