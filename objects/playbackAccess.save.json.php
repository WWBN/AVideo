<?php
require_once __DIR__ . '/../videos/configuration.php';
require_once __DIR__ . '/PlaybackAccess.php';
header('Content-Type: application/json');

if (!User::isAdmin()) {
    forbiddenPage('Only an admin can configure playback access', true);
}
forbidIfNotPost();
if (!isGlobalTokenValid()) {
    forbiddenPage('Invalid or missing CSRF token', true);
}
$response = ['error' => false, 'msg' => __('Playback access rule saved')];
try {
    PlaybackAccess::saveRule($_POST['scope'] ?? '', intval($_POST['scope_id'] ?? 0), $_POST['mode'] ?? '', $_POST['groups'] ?? []);
} catch (InvalidArgumentException $th) {
    $response = ['error' => true, 'msg' => __($th->getMessage())];
} catch (Throwable $th) {
    _error_log($th->getMessage(), AVideoLog::$ERROR);
    $response = ['error' => true, 'msg' => __('An error occurred')];
}
echo json_encode($response);
