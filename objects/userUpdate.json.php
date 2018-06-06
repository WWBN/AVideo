<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$user = new User(0);
$user->loadSelfUser();
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
$user->setAbout($_POST['about']);
$unique = $user->setChannelName($_POST['channelName']);
if(!$unique){
    echo '{"error":"'.__("Channel name already exists").'"}';
    exit;
}

if (User::isAdmin() && !empty($_POST['status'])) {
    $user->setStatus($_POST['status']);
}
echo '{"status":"'.$user->save().'"}';
User::updateSessionInfo();
