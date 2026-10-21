<?php
// Administrator tool: asks PayPal to deliver a webhook event again (it is handled idempotently).
// CLI: php resend.php WH-xxxxx  — or, as an administrator, a POST with event_id and globalToken.
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';

if (!isCommandLineInterface()) {
    if (!User::isAdmin()) {
        forbiddenPage('Permission denied', true);
    }
    forbidIfNotPost();
    forbidIfInvalidToken();
}
header('Content-Type: application/json');
$eventId = isCommandLineInterface() ? (string) ($argv[1] ?? '') : (string) ($_POST['event_id'] ?? '');
if (!preg_match('/^WH-[A-Z0-9-]{10,80}$/D', $eventId) || empty(AVideoPlugin::loadPluginIfEnabled("PayPalYPT"))) {
    die(json_encode(['error' => true, 'msg' => 'Invalid webhook event ID']));
}
$output = PayPalYPT::resendWebhook($eventId);
echo json_encode(['error' => empty($output), 'msg' => empty($output) ? 'PayPal did not accept the resend' : 'Resend requested']);
