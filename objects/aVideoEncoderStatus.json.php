<?php
// Handle the optional account fallback once below, so a rate-limit result can be
// reported accurately instead of being lost in bootstrap's automatic login.
$_REQUEST['do_not_login'] = 1;
require_once __DIR__ . '/../videos/configuration.php';
require_once __DIR__ . '/EncoderJobStatus.php';
header('Content-Type: application/json');
forbidIfNotPost();

$videosId = filter_var($_POST['videos_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$hash = $_POST['video_id_hash'] ?? '';
if (!$videosId || !is_string($hash) || strlen($hash) > 4096) {
    http_response_code(400);
    echo json_encode(['error' => true, 'code' => 'invalid_request', 'msg' => 'Invalid job status request.']);
    exit;
}
if (!User::isLogged() && isset($_POST['user'], $_POST['pass'])) {
    if (!is_string($_POST['user']) || !is_string($_POST['pass']) || strlen($_POST['pass']) > 4096) {
        http_response_code(400);
        echo json_encode(['error' => true, 'code' => 'invalid_request', 'msg' => 'Invalid account credentials.']);
        exit;
    }
    $account = new User(0, $_POST['user'], $_POST['pass']);
    $loginResult = $account->login(false, true);
    if ($loginResult === User::TOO_MANY_FAILED_ATTEMPTS) {
        http_response_code(429);
        header('Retry-After: 900');
        echo json_encode(['error' => true, 'code' => 'authentication_throttled', 'retryable' => true,
            'msg' => 'Site login is temporarily rate limited. Recovery will retry automatically after a delay.']);
        exit;
    }
    if ($loginResult === User::SYSTEM_ERROR) {
        http_response_code(503);
        echo json_encode(['error' => true, 'code' => 'streamer_unavailable', 'retryable' => true]);
        exit;
    }
}
echo json_encode(EncoderJobStatus::inspect($videosId, $hash));
