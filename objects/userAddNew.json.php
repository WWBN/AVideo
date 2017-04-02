<?php
header('Content-Type: application/json');
require_once 'user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}
$user = new User(@$_POST['id']);
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
$user->setIsAdmin($_POST['isAdmin']);
echo '{"status":"'.$user->save().'"}';