<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
$user = new User(@$_POST['id']);
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
$user->setIsAdmin($_POST['isAdmin']);
$user->setCanStream($_POST['canStream']);
$user->setCanUpload($_POST['canUpload']);
$user->setStatus($_POST['status']);
$user->setUserGroups(@$_POST['userGroups']);
echo '{"status":"'.$user->save(true).'"}';
