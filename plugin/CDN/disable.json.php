<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
if (empty($obj)) {
    $resp->msg = 'Disable: CDN Plugin disabled, please enable it and clear the cache';
    die(json_encode($resp));
}

// Key must be pre-configured by admin; reject all requests until it is set.
if (empty($obj->key)) {
    $resp->msg = 'CDN key not configured';
    die(json_encode($resp));
}
// Constant-time comparison to prevent timing side-channel on the shared secret.
if (empty($_REQUEST['key']) || !hash_equals($obj->key, $_REQUEST['key'])) {
    $resp->msg = 'Key does not match';
    die(json_encode($resp));
}

$row = Plugin::getPluginByName('CDN');

$cdn = new Plugin($row['id']);
$cdn->setStatus('inactive');
$id = $cdn->save();
$resp->error = empty($id);

die(json_encode($resp));
