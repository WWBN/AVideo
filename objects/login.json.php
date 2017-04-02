<?php

require_once 'user.php';
header('Content-Type: application/json');
$user = new User(0, $_POST['user'], $_POST['pass']);
$user->login();
$object = new stdClass();
$object->isLogged = User::isLogged();
echo json_encode($object);