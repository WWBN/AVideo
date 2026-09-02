<?php
require_once '../../videos/configuration.php';
$obj = AVideoPlugin::getDataObjectIfEnabled("LoginControl");

if (empty($obj)) {
    forbiddenPage("Plugin disabled");
}

// SECURITY: this endpoint is not *.json.php, so the default autoRateLimitGuard() never covers it.
enforceRateLimit('logincontrol_confirm', 20, 300);

if (!empty($_REQUEST['confirmation'])) {
    if (LoginControl::validateConfirmationCodeHash($_REQUEST['confirmation'])) {
        header("Location: {$global['webSiteRootURL']}user?msg=". urlencode(__("Your device is confirmed")));
        exit;
    }
}

if (empty($users_id)) {
    if (!User::isLogged()) {
        gotToLoginAndComeBackHere("");
    } else {
        $users_id = User::getId();
    }
}

if (empty($_REQUEST['confirmation_code'])) {
    forbiddenPage("Confirmation code not found");
}

if (LoginControl::confirmCode($users_id, $_REQUEST['confirmation_code'])) {
    header("Location: {$global['webSiteRootURL']}user?msg=". urlencode(__("Your device is confirmed")));
}
