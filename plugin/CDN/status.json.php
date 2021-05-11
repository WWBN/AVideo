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

// Update S3 CDN
$resp->CDN_S3 = CDN::getS3URL();

// Update B2 CDN
$resp->CDN_B2 = CDN::getB2URL();

// Update FTP CDN
$resp->CDN_FTP = CDN::getFTPURL();

// Update YPT Storage CDN
$resp->CDN_YPTStorage = array();
$plugin = AVideoPlugin::getDataObjectIfEnabled('YPTStorage');
if (!empty($plugin)) {
    $rows = Sites::getAllActive();
    foreach ($rows as $value) {
        if (empty($value['url'])) {
            continue;
        }
        $resp->CDN_YPTStorage[] = array(
                'id'=>$value['id'], 
                'url'=>addLastSlash($value['url'])
            );
    }
}

// Update Live CDN
$resp->CDN_Live = '';
$resp->CDN_LiveServers = array();
$plugin = AVideoPlugin::getDataObjectIfEnabled('Live');
if (!empty($plugin)) {
    if ($plugin->useLiveServers) {
        $rows = Live_servers::getAllActive();
        foreach ($rows as $value) {
            if (empty($value['playerServer'])) {
                continue;
            }
            $resp->CDN_LiveServers[] = array(
                'id'=>$value['id'], 
                'url'=>addLastSlash($value['playerServer'])
            );
        }
    } else {
        $resp->CDN_Live = addLastSlash($plugin->playerServer);
    }
}

// Update Liveservers CDN
$cdn = AVideoPlugin::loadPluginIfEnabled('CDN');
$id = $cdn->setDataObject($obj);
if (!empty($id)) {
    $resp->error = false;
}

die(json_encode($resp));
