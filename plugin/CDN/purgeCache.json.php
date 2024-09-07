<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');

if(!User::isAdmin()){
    forbiddenPage('Must be admin');
}

$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
if (empty($obj)) {
    $resp->msg = 'Disable: CDN Plugin disabled, please enable it and clear the cache';
    die(json_encode($resp));
}

$resp->purgeResponse = CDN::purgeCache();
$resp->purgeResponseObj = _json_decode($resp->purgeResponse);

die(json_encode($resp));
