<?php

// ### Obtain Access Token From Refresh Token

require __DIR__ . '/../bootstrap.php';
use PayPal\Api\OpenIdTokeninfo;

// You can retrieve the refresh token by executing ObtainUserConsent.php and store the refresh token
$refreshToken = 'yzX4AkmMyBKR4on7vB5he-tDu38s24Zy-kTibhSuqA8kTdy0Yinxj7NpAyULx0bxqC5G8dbXOt0aVMlMmtpiVmSzhcjVZhYDM7WUQLC9KpaXGBHyltJPkLLQkXE';

try {

    $tokenInfo = new OpenIdTokeninfo();
    $tokenInfo = $tokenInfo->createFromRefreshToken(array('refresh_token' => $refreshToken), $apiContext);

} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Obtained Access Token From Refresh Token", "Access Token", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Obtained Access Token From Refresh Token", "Access Token", $tokenInfo->getAccessToken(), null, $tokenInfo);
