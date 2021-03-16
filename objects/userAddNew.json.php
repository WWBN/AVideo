<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
$_REQUEST["do_not_login"]=1;
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!Permissions::canAdminUsers()) {
    die('{"error":"'.__("Permission denied").'"}');
}
session_write_close();
if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
    $_POST['email'] = $_POST['user'];
}

if (empty($_POST['id'])) {
    _error_log("userAddNew.json.php: Adding a user");
} else {
    _error_log("userAddNew.json.php: Editing a user id = {$_POST['id']}");
}

$user = new User(@$_POST['id']);
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
$user->setIsAdmin($_POST['isAdmin']);
$user->setCanStream($_POST['canStream']);
$user->setCanUpload($_POST['canUpload']);
$user->setCanViewChart($_POST['canViewChart']);
$user->setCanCreateMeet($_POST['canCreateMeet']);
$user->setStatus($_POST['status']);
$user->setEmailVerified($_POST['isEmailVerified']);
$user->setAnalyticsCode($_POST['analyticsCode']);

_error_log("userAddNew.json.php: set channel name = ({$_POST['channelName']})");

if(empty($_POST['channelName'])){
    $_POST['channelName'] = $_POST['user'];
}

$unique = $user->setChannelName($_POST['channelName']);

//identify what variables come from external plugins
$userOptions=AVideoPlugin::getPluginUserOptions();
if (is_array($userOptions)) {
    $externalOptions=array();
    foreach ($userOptions as $uo => $id) {
        if (isset($_POST[$id])) {
            $externalOptions[$id]=$_POST[$id];
        }
    }
    $user->setExternalOptions($externalOptions);
}
//save it

if (!empty($_POST['channelName']) && !$unique) {
    _error_log("userAddNew.json.php: channel name already exits = ({$_POST['channelName']})");
    $user->setChannelName(User::_recommendChannelName($_POST['channelName']));
    _error_log("userAddNew.json.php: new channel name: ".$user->getChannelName());
}

if (empty($_POST['userGroups'])) {
    if (empty($_POST['id']) && !empty($advancedCustomUser->userDefaultUserGroup->value)) { // for new users use the default usergroup
        $user->setUserGroups(array($advancedCustomUser->userDefaultUserGroup->value));
    }
} else {
    $user->setUserGroups($_POST['userGroups']);
}

_error_log("userAddNew.json.php: saving");
$users_id = $user->save(true);
echo '{"status":"'.$users_id.'"}';
_error_log("userAddNew.json.php: saved users_id ($users_id)");
