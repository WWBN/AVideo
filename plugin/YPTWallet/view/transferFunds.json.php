<?php
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../';
}
require_once $global['systemRootPath'] . 'objects/captcha.php';
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->walletBalance = 0;

if (!User::isLogged()) {
    $obj->msg = ("Is not logged");
    die(json_encode($obj));
}
$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
if(empty($plugin)){
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}
$obj->walletBalance = $plugin->getBalanceFormated(User::getId());
$valid = Captcha::validation($_POST['captcha']);
if (!$valid) {
    $obj->msg = ("Invalid Captcha");
    die(json_encode($obj));
}

if(empty($_POST['users_id'])){
    $obj->msg = ("User Not defined");
    die(json_encode($obj));
}

$_POST['value'] = floatval($_POST['value']);


if($plugin->transferBalance(User::getId(),$_POST['users_id'], $_POST['value'])){
    $obj->error = false;
}else{
    $obj->msg = "We could not transfer funds, please check your balance";
}
$obj->walletBalance = $plugin->getBalanceFormated(User::getId());

echo json_encode($obj);