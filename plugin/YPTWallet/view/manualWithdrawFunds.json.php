<?php

require_once __DIR__ . '/../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isLogged()) {
    $obj->msg = ("Is not logged");
    die(json_encode($obj));
}

$plugin = AVideoPlugin::loadPluginIfEnabled("YPTWallet");
$dataObj = $plugin->getDataObject();
if (empty($plugin)) {
    $obj->msg = ("Plugin not enabled");
    die(json_encode($obj));
}

// Get the withdrawal value and calculate the fee
$value = floatval($_POST['value']);
$cutPercentage = !empty($dataObj->withdrawFundsSiteCutPercentage->value) ? floatval($dataObj->withdrawFundsSiteCutPercentage->value) : 0;
$cutValue = ($value * $cutPercentage) / 100;
$finalValue = $value - $cutValue;

if (!empty($dataObj->enableAutoWithdrawFundsPagePaypal)) {
    $paypal = AVideoPlugin::loadPluginIfEnabled("PayPalYPT");
    if (!empty($paypal)) {
        $obj = PayPalYPT::WalletPayout(User::getId(), $finalValue); // Use the final value after cut
    }
} else {
    if (YPTWallet::transferBalance(User::getId(), $dataObj->manualWithdrawFundsTransferToUserId, $value)) {
        //send an email
        $emailsArray = array();
        $emailsArray[] = $dataObj->manualWithdrawFundsNotifyEmail;

        $subject = $config->getWebSiteTitle() . " " . $dataObj->manualWithdrawFundsPageButton . " from: " . User::getUserName();

        $wallet = $plugin->getOrCreateWallet(User::getId());
        $wallet_id = $wallet->getId();
        $url = "{$global['webSiteRootURL']}plugin/YPTWallet/view/history.php?users_id=" . User::getId();
        $message = "<strong>" . YPTWallet::MANUAL_WITHDRAW . "</strong> user <strong><a href='{$url}'>[" . User::getId() . "]" . User::getUserName() . "</a></strong> withdrawal of " . YPTWallet::formatCurrency($finalValue) . " (cut: {$cutPercentage}%, " . YPTWallet::formatCurrency($cutValue) . ")";

        $_POST['information'] = strip_tags($_POST['information']);

        $emailMessage = "The user <a href='{$url}'>[" . User::getId() . "] <strong>" . User::getUserName() . "</strong></a> requested a <strong style='color:#A00;'>" . YPTWallet::MANUAL_WITHDRAW . "</strong> value of <strong>".YPTWallet::formatCurrency($value)."</strong>";

        if (!empty($cutPercentage)) {
            $emailMessage .= "<br><strong>Cut percentage:</strong> {$cutPercentage}%";
        }

        if (!empty($cutValue)) {
            $emailMessage .= "<br><strong>Amount deducted:</strong> " . YPTWallet::formatCurrency($cutValue);
        }

        if (!empty($finalValue)) {
            $emailMessage .= "<br><strong>Final withdrawal amount:</strong> " . YPTWallet::formatCurrency($finalValue);
        }

        $emailMessage .= "<hr><strong>Date: </strong> " . date("Y-m-d h:i:s");

        if (!empty($_POST['information'])) {
            $emailMessage .= "<br><strong>Information: </strong> " . nl2br($_POST['information']);
        }

        if (!empty($wallet->getCrypto_wallet_address())) {
            $emailMessage .= "<br><strong>{$dataObj->CryptoWalletName}: </strong> " . $wallet->getCrypto_wallet_address();
        }


        $json = array(
            'value' => $value,
            'cutPercentage' => $cutPercentage,
            'cutValue' => $cutValue,
            'finalValue' => $finalValue,
        );
        _error_log('WalletLog::addLog start');
        $balance = $wallet->getBalance();
        $obj->addLog = WalletLog::addLog($wallet_id, $finalValue, $balance, $message, json_encode($json), "pending", YPTWallet::MANUAL_WITHDRAW, $emailMessage);

        if (!empty($obj->addLog)) {
            _error_log('WalletLog::addLog line='.__LINE__);
            //YPTWallet::transactionNotification(User::getId(), $dataObj->manualWithdrawFundsTransferToUserId, $finalValue, 'pending');
            _error_log('WalletLog::addLog line='.__LINE__);
            //$plugin->sendEmails($emailsArray, $subject, $emailMessage . "");
            _error_log('WalletLog::addLog line='.__LINE__);
            $obj->error = false;
        } else {
            _error_log('WalletLog::addLog line='.__LINE__);
            $obj->msg = "Something is wrong, contact the admin";
        }
        _error_log('WalletLog::addLog end');
    } else {
        $obj->msg = "We could not transfer funds, please check your balance";
    }
}

$obj->walletBalance = $plugin->getBalanceFormated(User::getId());
die(json_encode($obj));
