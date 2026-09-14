<?php
require_once __DIR__ . '/../../videos/configuration.php';
header('Content-Type: application/json');

$key = is_string($_POST['key'] ?? null) ? $_POST['key'] : '';
$serverId = (int) ($_POST['live_servers_id'] ?? 0);
if (!AVideoPlugin::isEnabledByName('Live') || $key === '' ||
    !verifyToken($_POST['globalToken'] ?? '', 'live-audience:' . $key . ':' . $serverId)) {
    forbiddenPage('Invalid live audience request', true);
}
if (isBot() || session_id() === '') {
    die(json_encode(['error' => false, 'active' => false]));
}
try {
    // Resolve each time: the watch page may have been opened before this broadcast started.
    $history = LiveTransmitionHistory::getLatest($key, $serverId, true);
    if (empty($history['id'])) {
        die(json_encode(['error' => false, 'active' => false]));
    }
    $historyId = (int) $history['id'];
    LiveTransmitionHistoryLog::addLog($historyId);
    $saved = LiveTransmitionHistory::recordAudienceSample($historyId, 45);
    if ($saved === false) {
        throw new RuntimeException('Unable to save live audience sample');
    }
    echo json_encode(['error' => false, 'active' => true]);
} catch (Throwable $e) {
    _error_log('Live audience sample failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => true, 'msg' => 'Unable to record viewer activity']);
}
