<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->users_id = 0;

if (!User::isLogged()) {
    $obj->msg = __("Is not logged");
    die(json_encode($obj));
}
$_REQUEST["do_not_login"]=1;
require_once $global['systemRootPath'] . 'objects/user.php';
$user = new User(0);
$user->loadSelfUser();
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
$user->setAbout($_POST['about']);
$user->setAnalyticsCode($_POST['analyticsCode']);
$user->setDonationLink($_POST['donationLink']);
$user->setPhone($_POST['phone']);
$unique = $user->setChannelName($_POST['channelName']);
if (!$unique) {
    $obj->msg = __("Channel name already exists");
    die(json_encode($obj));
}

if (empty($user->getBdId())) {
    $obj->msg = __("User not found");
    die(json_encode($obj));
}

if (!empty($advancedCustomUser->emailMustBeUnique)) {
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $obj->msg = __("You must specify a valid email")." {$_POST['email']} (update)";
        die(json_encode($obj));
    }
    $userFromEmail = User::getUserFromEmail($_POST['email']);
    if (!empty($userFromEmail) && $userFromEmail['id'] !== $user->getBdId()) {
        $obj->msg = __("Email already exists");
        die(json_encode($obj));
    }
}

if (User::isAdmin() && !empty($_POST['status'])) {
    $user->setStatus($_POST['status']);
}

$obj->users_id = $user->save();

$obj->error = empty($obj->users_id);
User::updateSessionInfo();
die(json_encode($obj));