<?php

require_once 'subscribe.php';
if(!User::isLogged()){
    return false;
}
header('Content-Type: application/json');

$user_id = User::getId();
// if admin bring all subscribers
if(User::isAdmin()){
    $user_id = "";
}
$Subscribes = Subscribe::getAllSubscribes($user_id);
$total = Subscribe::getTotalSubscribes($user_id);
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($Subscribes).'}';