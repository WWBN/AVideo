<?php

// ### Get Web Profile
// If your request is successful, the API returns a web_profile object response that contains the profile details.
// Documentation available at https://developer.paypal.com/webapps/developer/docs/api/#retrieve-a-web-experience-profile

// We are going to re-use the sample code from CreateWebProfile.php.
// If you have not visited the sample yet, please visit it before trying GetWebProfile.php
// The CreateWebProfile.php will create a web profile for us, and return a CreateProfileResponse,
// that contains the web profile ID.
/** @var \PayPal\Api\CreateProfileResponse $result */
$createProfileResponse = require 'CreateWebProfile.php';

try {
    // If your request is successful, the API returns a web_profile object response that contains the profile details.
    $webProfile = \PayPal\Api\WebProfile::get($createProfileResponse->getId(), $apiContext);
} catch (\PayPal\Exception\PayPalConnectionException $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Get Web Profile", "Web Profile", $webProfile->getId(), null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Get Web Profile", "Web Profile", $webProfile->getId(), null, $webProfile);

return $webProfile;
