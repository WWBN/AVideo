<?php
global $global, $config;
$global['ignoreUserMustBeLoggedIn'] = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
_error_log('Email verification starts');
require_once $global['systemRootPath'] . 'objects/user.php';
enforceRateLimit('sendVerificationLink', 3, 3600);
$obj = new stdClass();
$obj->error = true;
$obj->msg = "Unknown error";

if (!empty($_GET['users_id'])) {
    // Only the account owner (or an admin) may trigger a (re)send for a given
    // users_id - anyone else falls through to the generic "Unknown error"
    // response above, same as a nonexistent account, so this can't be used as
    // an account-existence/verification-status oracle either.
    if (User::isLogged() && (intval(User::getId()) === intval($_GET['users_id']) || User::isAdmin())) {
        $user = new User($_GET['users_id']);
        $verified = $user->getEmailVerified();
        if (empty($verified)) {
            if (User::sendVerificationLink($_GET['users_id'])) {
                $obj->error = false;
                $obj->msg = __("Verification Sent");
            }
        } else {
            $obj->msg = __("Already verified");
        }
    }
    _error_log('Email verification 1 '.$obj->msg);
} elseif (!empty($_GET['code'])) {
    $result = User::verifyCode($_GET['code']);

    if ($result) {
        $msg = __("Email Verified");
        _error_log('Email verification 2 '.$msg);
        header("Location: {$global['webSiteRootURL']}?success={$msg}");
        exit;
    } else {
        $msg = __("Email verification error");
        _error_log('Email verification 3 '.$msg);
        header("Location: {$global['webSiteRootURL']}user?error={$msg}");
        exit;
    }
}

header('Content-Type: application/json');
die(json_encode($obj));
