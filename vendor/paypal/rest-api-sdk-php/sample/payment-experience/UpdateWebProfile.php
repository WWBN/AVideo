<?php

// #### Update Web Profile
// Use this call to update an experience profile.
// Documentation available at https://developer.paypal.com/webapps/developer/docs/api/#update-a-web-experience-profile

// We will be re-using the sample code to get a web profile. GetWebProfile.php will
// create a new web profileId for sample, and return the web profile object.
/** @var \PayPal\Api\WebProfile $webProfile */
$webProfile = require 'GetWebProfile.php';


// Updated the logo image of presentation object in a given web profile.
$webProfile->getPresentation()->setLogoImage("http://www.google.com/favico.ico");

try {
    // Update the web profile to change the logo image.
    if ($webProfile->update($apiContext)) {
        // If the update is successfull, we can now get the object, and verify the web profile
        // object
        $updatedWebProfile = \PayPal\Api\WebProfile::get($webProfile->getId(), $apiContext);
    }
} catch (\Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Updated Web Profile", "Web Profile", $webProfile->getId(), $webProfile, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Updated Web Profile", "Web Profile", $updatedWebProfile->getId(), $webProfile, $updatedWebProfile);
