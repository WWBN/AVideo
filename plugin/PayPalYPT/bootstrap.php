<?php
require_once $global['systemRootPath'].'plugin/YouPHPTubePlugin.php';
$obj = YouPHPTubePlugin::getObjectData("PayPalYPT");
// 1. Autoload the SDK Package. This will include all the files and classes to your autoloader
// Used for composer based installation
require __DIR__  . '/vendor/autoload.php';
// Use below for direct download installation
// require __DIR__  . '/PayPal-PHP-SDK/autoload.php';
// After Step 1

/**
 *  
 * https://stackoverflow.com/questions/20494944/how-to-create-clientid-and-clientsecret-for-oauthtokencredential-paypal-rest-api
You need to follow the following steps:

    Go to Paypal developer Site here https://developer.paypal.com/developer/applications (you must have Paypal account, of course)
    Click on Create App on right side of page
    Choose name of your app and click Create App
    Now you can see and manage everything include client ID and secret.

 */

$apiContext = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $obj->ClientID,     // ClientID
            $obj->ClientSecret      // ClientSecret
        )
);

// Step 2.1 : Between Step 2 and Step 3
$apiContext->setConfig(
      array(
        'log.LogEnabled' => true,
        'log.FileName' => $global['systemRootPath'].'videos/PayPal.log',
        'log.LogLevel' => 'DEBUG'
      )
);