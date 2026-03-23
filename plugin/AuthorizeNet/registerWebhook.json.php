<?php
/**
 * Admin-only endpoint to manually (re)register the AuthorizeNet webhook.
 * Use this after changing API credentials in the plugin settings.
 *
 * GET/POST /plugin/AuthorizeNet/registerWebhook.json.php
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => true, 'msg' => 'Permission denied']);
    exit;
}

require_once $global['systemRootPath'] . 'plugin/AuthorizeNet/AuthorizeNet.php';

$cfg = AVideoPlugin::getDataObject('AuthorizeNet');
if (empty($cfg->apiLoginId) || empty($cfg->transactionKey) || empty($cfg->signatureKey)) {
    echo json_encode(['error' => true, 'msg' => 'AuthorizeNet credentials not fully configured. Set API Login ID, Transaction Key and Signature Key first.']);
    exit;
}

$result = AuthorizeNet::createWebhookIfNotExists(['net.authorize.payment.authcapture.created']);

$response = [
    'error'      => !empty($result['error']),
    'msg'        => $result['msg'] ?? null,
    'webhookId'  => $result['webhookId'] ?? null,
    'status'     => $result['status'] ?? null,
    'webhookUrl' => AuthorizeNet::getWebhookURL(),
];

_error_log('[AuthorizeNet] Manual webhook registration: ' . json_encode($response));

echo json_encode($response);
