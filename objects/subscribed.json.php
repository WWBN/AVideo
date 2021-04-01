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

$Subscribes = Subscribe::getSubscribedChannels($user_id);
$total = Subscribe::getTotalSubscribedChannels($user_id);
echo '{  "current": '. getCurrentPage().',"rowCount": '.getRowCount().', "total": '.$total.', "rows":'. json_encode($Subscribes).'}';
