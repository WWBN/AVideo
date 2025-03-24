<?php

require __DIR__ . '/../bootstrap.php';

use PayPal\Api\OpenIdTokeninfo;
use PayPal\Exception\PayPalConnectionException;

session_start();

// ### User Consent Response
// PayPal would redirect the user to the redirect_uri mentioned when creating the consent URL.
// The user would then able to retrieve the access token by getting the code, which is returned as a GET parameter.
if (isset($_GET['success']) && $_GET['success'] == 'true') {

    $code = $_GET['code'];

    try {
        // Obtain Authorization Code from Code, Client ID and Client Secret
        $accessToken = OpenIdTokeninfo::createFromAuthorizationCode(array('code' => $code), null, null, $apiContext);
    } catch (PayPalConnectionException $ex) {
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Obtained Access Token", "Access Token", null, $_GET['code'], $ex);
        exit(1);
    }

    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Obtained Access Token", "Access Token", $accessToken->getAccessToken(), $_GET['code'], $accessToken);

}
