<?php
require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');
require_once __DIR__ . '/LocalEncoderLogin.php';

forbidIfNotPost();
if (empty(CustomizeAdvanced::getLocalEncoderURL())) {
    forbiddenPage('Local Encoder is disabled', true);
}
$action = $_POST['action'] ?? 'issue';
if ($action !== 'redeem') {
    if (!User::isLogged() || !User::canUpload()) {
        forbiddenPage('Upload permission required', true);
    }
    // A browser must prove same-origin intent. The headless Encoder instead supplies verified credentials.
    if (empty($global['loggedInFromRequestCredentials']) && !isGlobalTokenValid()) {
        forbiddenPage('Invalid or missing CSRF token', true);
    }
}
$response = array('error' => true, 'msg' => __('Open Local Encoder from your site again, or sign in manually.'));
try {
    $grants = new LocalEncoderLogin();
    if ($action === 'issue') {
        $user = new User(User::getId());
        $response = array('error' => false, 'code' => $grants->issue(User::getId(), hash('sha256', $user->getPassword()), $_POST['challenge'] ?? ''), 'expires_in' => LocalEncoderLogin::TTL);
    } elseif ($action === 'redeem') {
        $grant = $grants->consume($_POST['code'] ?? '', $_POST['verifier'] ?? '');
        if ($grant) {
            $user = new User($grant['users_id']);
            if ($user->getStatus() === 'a' && hash_equals($grant['password_version'], hash('sha256', $user->getPassword()))) {
                $response = array('error' => false, 'user' => $user->getUser(), 'pass' => User::getUserHash($grant['users_id'], '+1 day'));
            }
        }
    }
} catch (Throwable $th) {
    _error_log('Local Encoder authorization failed (' . get_class($th) . ')', AVideoLog::$ERROR);
}
echo json_encode($response);
