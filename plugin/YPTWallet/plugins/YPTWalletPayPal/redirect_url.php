<?php

header('Content-Type: application/json');

if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$plugin = YouPHPTubePlugin::loadPluginIfEnabled("YPTWallet");
$paypal = YouPHPTubePlugin::loadPluginIfEnabled("PayPalYPT");
// how to get the users_ID from the PayPal call back IPN?
$users_id = User::getId();

//check if there is a token and this token has a user (recurrent payments)
if (!empty($_GET['token'])) {
    $row = PayPalSubscription::getFromToken($_GET['token']);
    if (!empty($row)) {
        $users_id = $row['users_id'];
    } else {
        if (!empty($users_id)) {
            //save token
            $p = new PayPalSubscription(0);
            $p->setToken($_GET['token']);
            $p->setUsers_id($users_id);
            $p->save();
        }
    }
}

if (empty($users_id)) {
    error_log("Redirect URL error, Not found user or token");
    die();
}

$invoiceNumber = uniqid();

$payment = $paypal->execute();
//var_dump($amount);
$obj = new stdClass();
$obj->error = true;
if (!empty($payment)) {
    $amount = PayPalYPT::getAmountFromPayment($payment);
    $plugin->addBalance($users_id, $amount->total, "Paypal payment", json_encode($payment));
    $obj->error = false;
    if (!empty($_SESSION['addFunds_Success'])) {
        header("Location: {$_SESSION['addFunds_Success']}");
        unset($_SESSION['addFunds_Success']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=success");
    }
} else {
    if (!empty($_SESSION['addFunds_Fail'])) {
        header("Location: {$_SESSION['addFunds_Fail']}");
        unset($_SESSION['addFunds_Fail']);
    } else {
        header("Location: {$global['webSiteRootURL']}plugin/YPTWallet/view/addFunds.php?status=fail");
    }
}
error_log(json_encode($obj));
error_log("PAYPAL redirect_url GET:  " . json_encode($_GET));
error_log("PAYPAL redirect_url POST: " . json_encode($_POST));
?>