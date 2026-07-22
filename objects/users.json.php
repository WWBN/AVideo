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
// Cap rowCount for callers without user-search permissions to prevent
// bulk harvesting of the full account list in a single request.
if (!canSearchUsers()) {
    $_REQUEST['rowCount'] = min($_REQUEST['rowCount'], 100);
}
if (!empty($_REQUEST['users_id'])) {
    //echo __LINE__, PHP_EOL;
    $requestedId = $_REQUEST['users_id'];
    // Only admins/search-capable users, or a logged-in user fetching their own
    // record, may use this path. Without this gate the endpoint is a sequential-ID
    // existence oracle: unauthenticated callers can enumerate every account by ID.
    if (!canSearchUsers() && (!User::isLogged() || $requestedId !== User::getId())) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'forbidden', 'current' => 1, 'rowCount' => 0, 'total' => 0, 'rows' => []]);
        exit;
    }
    $user = User::getUserFromID($requestedId);
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
            // Do NOT set $ignoreAdmin = true here. Doing so allows any unauthenticated
            // caller to bypass the admin-only guard inside User::getAllUsers() by
            // simply submitting isCompany=0/1. The $ignoreAdmin flag is already
            // correctly set by canSearchUsers() above; honour that value.
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
            // Defense-in-depth: the admin grid renders values via innerHTML
            // (bootgrid). $value is the raw DB row, which bypasses the sanitizing
            // User::getPhone() getter, so neutralize any stored markup here to
            // cover rows saved before setPhone() was hardened. Prevents stored XSS.
            if (isset($u['phone'])) {
                $u['phone'] = strip_tags($u['phone']);
            }
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
