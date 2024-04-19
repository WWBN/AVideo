<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$global['showChannelPhotoOnVideoItem'] = 1; 
$global['showChannelNameOnVideoItem'] = 1;
header('Content-Type: application/json');
session_write_close();
$canAdminUsers = canAdminUsers(); 
if (empty($_POST['current'])) {
    $_POST['current'] = 1;
}
if (empty($_REQUEST['rowCount'])) {
    $_REQUEST['rowCount'] = 10;
}
if (!empty($_REQUEST['users_id'])) {
    //echo __LINE__, PHP_EOL;
    $user = User::getUserFromID($_REQUEST['users_id']);
    if (!empty($user)) {
        $users = [$user];
        $total = 1;
    } else {
        $users = [];
        $total = 0;
    }
} else if (empty($_REQUEST['user_groups_id'])) {
    //echo __LINE__, PHP_EOL;
    $isAdmin = null;
    $isCompany = null;
    $canUpload = null;
    $ignoreAdmin = canSearchUsers() ? true : false;
    if (isset($_REQUEST['isAdmin'])) {
        $isAdmin = 1;
    }
    if (isset($_REQUEST['isCompany'])) {
        $isCompany = intval($_REQUEST['isCompany']);
        if (!$canAdminUsers) {
            if (User::isACompany()) {
                $isCompany = 0;
            } else {
                $isCompany = 1;
            }
            $ignoreAdmin = true;
        }
    }
    if (isset($_REQUEST['canUpload'])) {
        $canUpload = intval($_REQUEST['canUpload']);
    }    
    $users = User::getAllUsers($ignoreAdmin, ['name', 'email', 'user', 'channelName', 'about'], @$_GET['status'], $isAdmin, $isCompany);
    $total = User::getTotalUsers($ignoreAdmin, @$_GET['status'], $isAdmin, $isCompany);
} else {
    //echo __LINE__, PHP_EOL;
    $users = User::getAllUsersFromUsergroup($_REQUEST['user_groups_id'], canSearchUsers() ? true : false, ['name', 'email', 'user', 'channelName', 'about'], @$_GET['status']);
    $total = User::getTotalUsersFromUsergroup($_REQUEST['user_groups_id'], canSearchUsers() ? true : false, @$_GET['status']);
}
//var_dump($user);exit;
//echo examineJSONError($users);exit;
if (empty($users)) {
    $json = '[]';
    $total = 0;
} else {
    foreach ($users as $key => $value) {
        if(!$canAdminUsers){
            $u = [];
            $u['id'] = $value['id'];
            //$u['user'] = $user['user'];
            $u['identification'] = $value['identification'];
            $u['photo'] = $value['photo'];
            $u['background'] = $value['background'];
            $u['status'] = $value['status'];
        }else{
            $u = $value;
        }
        if(!empty($u['usageInBytes'])){
            $u['usageTxt'] = humanFileSize($u['usageInBytes']);            
        }else{
            $u['usageInBytes'] = 0;
            $u['usageTxt'] = '0 bytes';
        }
        if(empty($u['creator'])){
            $u['creator'] = Video::getCreatorHTML($u['id'], '', true, true);
        }
        if(empty($u['photo'])){
            $u['photo'] = User::getPhoto($u['id']);
        }
        $users[$key] = $u;
    }

    $json = _json_encode($users);
}
//var_dump($users, $json);

echo '{  "current": ' . $_POST['current'] . ',"rowCount": ' . $_REQUEST['rowCount'] . ', "total": ' . $total . ', "rows":' . $json . '}';
