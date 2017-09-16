<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
$user = new User(0);
$user->loadSelfUser();
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
if (User::isAdmin() && !empty($_POST['status'])) {
    $user->setStatus($_POST['status']);
}
echo '{"status":"'.$user->save().'"}';
User::updateSessionInfo();
