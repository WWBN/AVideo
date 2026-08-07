<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

// Rate limiting: max 20 calls per 5 minutes per caller IP.
// An encoder legitimately hashes a handful of passwords; anything higher
// indicates rainbow-table building or brute force.
enforceRateLimit('encryptPass', 20, 300);

// Admin-only. There is no way to give an Encoder a pre-shared secret for a
// Streamer it has never talked to before, so any token-based bypass here is
// necessarily forgeable from public information; the Encoder computes this
// transform locally instead (see .compose/encoder/objects/functions.php).
if (!User::isAdmin()) {
    http_response_code(401);
    $obj->msg = __('Unauthorized');
    die(json_encode($obj));
}

$obj->error = false;
// Intentionally omit the plaintext password from the response to avoid
// unnecessary information disclosure.
$obj->encryptedPassword = encryptPassword($_REQUEST['pass'] ?? '');

echo json_encode($obj);
