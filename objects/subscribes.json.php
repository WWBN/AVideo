<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'].'objects/subscribe.php';
if(!empty($_GET['user']) && !empty($_GET['pass'])){
    $user = new User(0, $_GET['user'], $_GET['pass']);
    $user->login(false, true);
}
if (!User::isLogged()) {
    return false;
}
header('Content-Type: application/json');

$user_id = User::getId();
// if admin bring all subscribers
if (User::isAdmin()) {
    $user_id = "";
}
$Subscribes = Subscribe::getAllSubscribes($user_id);
$total = Subscribe::getTotalSubscribes($user_id);
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($Subscribes).'}';
