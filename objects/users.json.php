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
if (!empty($_REQUEST['users_id'])) {
    //echo __LINE__, PHP_EOL;
    $user = User::getUserFromID($_REQUEST['users_id']);
    if (!empty($user)) {
        $users = array($user);
        $total = 1;
    } else {
        $users = array();
        $total = 0;
    }
} else if (empty($_REQUEST['user_groups_id'])) {
    //echo __LINE__, PHP_EOL;
    $isAdmin = null;
    $isCompany = null;
    $ignoreAdmin = $advancedCustomUser->userCanChangeVideoOwner ? true : false;
    if (isset($_REQUEST['isAdmin'])) {
        $isAdmin = 1;
    }
    if (isset($_REQUEST['isCompany'])) {
        $isCompany = intval($_REQUEST['isCompany']);
        if (!User::isAdmin()) {
            if (User::isACompany()) {
                $isCompany = 0;
            } else {
                $isCompany = 1;
            }
            $ignoreAdmin = true;
        }
    }
    $users = User::getAllUsers($ignoreAdmin, ['name', 'email', 'user', 'channelName', 'about'], @$_GET['status'], $isAdmin, $isCompany);
    $total = User::getTotalUsers($ignoreAdmin, @$_GET['status'], $isAdmin, $isCompany);
} else {
    //echo __LINE__, PHP_EOL;
    $users = User::getAllUsersFromUsergroup($_REQUEST['user_groups_id'], $advancedCustomUser->userCanChangeVideoOwner ? true : false, ['name', 'email', 'user', 'channelName', 'about'], @$_GET['status']);
    $total = User::getTotalUsersFromUsergroup($_REQUEST['user_groups_id'], $advancedCustomUser->userCanChangeVideoOwner ? true : false, @$_GET['status']);
}

//echo examineJSONError($users);exit;
if (empty($users)) {
    $json = '[]';
    $total = 0;
} else {
    foreach ($users as $key => $value) {
        if(!User::isAdmin()){
            $u = array();
            $u['id'] = $value['id'];
            //$u['user'] = $user['user'];
            $u['identification'] = $value['identification'];
            $u['photo'] = $value['photo'];
            $u['background'] = $value['background'];
            $u['status'] = $value['status'];
        }else{
            $u = $value;
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
