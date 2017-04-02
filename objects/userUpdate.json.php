<?php

require_once 'user.php';
header('Content-Type: application/json');
$user = new User(0);
$user->loadSelfUser();
$user->setUser($_POST['user']);
$user->setPassword($_POST['pass']);
$user->setEmail($_POST['email']);
$user->setName($_POST['name']);
echo '{"status":"'.$user->save().'"}';