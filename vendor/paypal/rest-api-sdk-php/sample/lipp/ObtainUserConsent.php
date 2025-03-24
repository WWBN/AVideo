<?php

require __DIR__ . '/../bootstrap.php';

use PayPal\Api\OpenIdSession;

$baseUrl = getBaseUrl() . '/UserConsentRedirect.php?success=true';

// ### Get User Consent URL
// The clientId is stored in the bootstrap file

//Get Authorization URL returns the redirect URL that could be used to get user's consent
$redirectUrl = OpenIdSession::getAuthorizationUrl(
    $baseUrl,
    array('openid', 'profile', 'address', 'email', 'phone',
        'https://uri.paypal.com/services/paypalattributes', 'https://uri.paypal.com/services/expresscheckout'),
    null,
    null,
    null,
    $apiContext
);

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Generated the User Consent URL", "URL", '<a href="'. $redirectUrl . '" >Click Here to Obtain User Consent</a>', $baseUrl, $redirectUrl);
