<?php
$closeSessionEarlyIncludeConfig = 1;
require_once dirname(__FILE__) . '/../../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->webSocketToken = "";
$obj->webSocketURL = "";

if(!AVideoPlugin::isEnabledByName("YPTSocket")){
    $obj->msg = "Socket plugin not enabled";
    die(json_encode($obj));
}

// SECURITY REVIEW (2026-08-21): intentionally unauthenticated - anonymous viewers need a
// socket token to receive live viewer counts/presence updates. This token still lets its
// holder send peer-to-peer messages (choosing to_users_id/resourceId) to any other
// connected client, including admins. That gadget chain is closed on the receiving end by
// the socket-callback allowlist in plugin/YPTSocket/script.js (registerSocketCallback()) -
// a peer can no longer invoke an arbitrary window function this way. Restricting this
// endpoint to logged-in users, or issuing anonymous tokens as read-only (no send capability),
// is a larger architecture change (touches Message.php/MessageSQLiteV2.php and the node
// socket server) left for maintainer judgement - do not silently narrow this without
// confirming it doesn't break the anonymous viewer-count/presence feature.
$obj->error = false;
$obj->webSocketToken = getEncryptedInfo(0);
$obj->webSocketURL = YPTSocket::getWebSocketURL();

die(json_encode($obj));
