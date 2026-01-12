<?php

require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CustomizeUser/Objects/Categories_has_users_groups.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->saved = 0;
$obj->users_id = User::getId();

$plugin = AVideoPlugin::loadPluginIfEnabled('CustomizeUser');

if (!User::isLogged()) {
    forbiddenPage('You need to be logged in');
}

// Sanitize donation buttons to prevent XSS
$donationButtonsList = @$_POST['donationButtonsList'];
if (!empty($donationButtonsList) && is_array($donationButtonsList)) {
    foreach ($donationButtonsList as $key => $button) {
        if (isset($button['label'])) {
            $donationButtonsList[$key]['label'] = strip_tags($button['label']);
        }
        if (isset($button['thankyou'])) {
            $donationButtonsList[$key]['thankyou'] = strip_tags($button['thankyou']);
        }
        if (isset($button['donationFlyIcon'])) {
            $donationButtonsList[$key]['donationFlyIcon'] = strip_tags($button['donationFlyIcon']);
        }
    }
}

$obj->saved = User::setDonationButtons($obj->users_id, $donationButtonsList);
if($obj->saved){
    $obj->error = false;
}

die(json_encode($obj));
