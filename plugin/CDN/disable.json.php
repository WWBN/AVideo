<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
session_write_close();
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
if (empty($obj)) {
    $resp->msg = 'CDN Plugin disabled';
    die(json_encode($resp));
}

if (empty($_REQUEST['key'])) {
    $resp->msg = 'Key is empty';
    die(json_encode($resp));
}

if (!empty($obj->key)) {
    //check the key
    if ($obj->key !== $_REQUEST['key']) {
        $resp->msg = 'Key Does not match';
        die(json_encode($resp));
    }
}
$obj->key = $_REQUEST['key'];
foreach ($_REQUEST['par'] as $key => $value) {
    $obj->{$key} = $value;
    $resp->{$key} = $value;
}

$row = Plugin::getPluginByName('CDN');

$cdn = new Plugin($row['id']);
$cdn->setStatus('inactive');
$id = $cdn->save();
$resp->error = empty($id);

die(json_encode($resp));
