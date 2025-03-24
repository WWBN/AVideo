<?php

// #### Partially Update Web Profile
// Use this call to partially update a web experience profile.
// Documentation available at https://developer.paypal.com/webapps/developer/docs/api/#partially-update-a-web-experience-profile

// We will be re-using the sample code to get a web profile. GetWebProfile.php will
// create a new web profileId for sample, and return the web profile object.
/** @var \PayPal\Api\WebProfile $webProfile */
$webProfile = require 'GetWebProfile.php';

// ### Create Patch Operation
// APIs allows us to pass an array of patches
// to make patch operations.
// Each Patch operation can be created by using Patch Class
// as shown below
$patchOperation1 = new \PayPal\Api\Patch();
// The operation to perform. Required. Allowed values: add, remove, replace, move, copy, test
$patchOperation1->setOp("add")
    // string containing a JSON-Pointer value that references a location within the target document (the target location) where the operation is performed. Required.
    ->setPath("/presentation/brand_name")
    // New value to apply based on the operation.
    ->setValue("New Brand Name");

// Similar patch operation to remove the landing page type
$patchOperation2 = new \PayPal\Api\Patch();
$patchOperation2->setOp("remove")
    ->setPath("/flow_config/landing_page_type");


//Generate an array of patch operations
$patches = array($patchOperation1, $patchOperation2);

try {
    // Execute the partial update, to carry out these two operations on a given web profile object
    if ($webProfile->partial_update($patches, $apiContext)) {
        $webProfile = \PayPal\Api\WebProfile::get($webProfile->getId(), $apiContext);
    }
} catch (\Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Partially Updated Web Profile", "Web Profile", $webProfile->getId(), $patches, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Partially Updated Web Profile", "Web Profile", $webProfile->getId(), $patches, $webProfile);
