<?php

require_once 'user.php';
header('Content-Type: application/json');
$users = User::getAllUsers();
$total = User::getTotalUsers();

echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($users).'}';