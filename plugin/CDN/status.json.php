<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';
_session_write_close();
header('Content-Type: application/json');

$resp = new stdClass();
$resp->error = true;
$resp->msg = '';
$obj = AVideoPlugin::getDataObjectIfEnabled('CDN');
if (empty($obj)) {
    $resp->msg = 'Status: CDN Plugin disabled, please enable it and clear the cache';
    die(json_encode($resp));
}

if (empty($_REQUEST['key'])) {
    $resp->msg = 'Key is empty';
    die(json_encode($resp));
}

// Keep the original first-time registration flow for marketplace bootstrap,
// but once a key exists we require a strict constant-time match.
if (!empty($obj->key) && !hash_equals($obj->key, $_REQUEST['key'])) {
    $resp->msg = 'Key does not match';
    die(json_encode($resp));
}

if (empty($obj->key)) {
    $obj->key = $_REQUEST['key'];
}

// Only CDN URL properties may be reported by edge nodes; credentials and key are admin-only.
$allowedPars = ['CDN', 'CDN_S3', 'CDN_B2', 'CDN_FTP', 'CDN_Live', 'CDN_YPTStorage', 'CDN_LiveServers'];
if (!empty($_REQUEST['par']) && is_array($_REQUEST['par'])) {
    foreach ($_REQUEST['par'] as $key => $value) {
        if (!in_array($key, $allowedPars, true)) {
            continue;
        }
        $obj->{$key} = $value;
        $resp->{$key} = $value;
    }
}

// Update S3 CDN
if (AVideoPlugin::isEnabledByName('AWS_S3')) {
    $resp->CDN_S3 = CDN::getCDN_S3URL();
} else {
    $resp->CDN_S3 = '';
}

// Update B2 CDN
if (AVideoPlugin::isEnabledByName('Blackblaze_B2')) {
    $resp->CDN_B2 = CDN::getCDN_B2URL();
} else {
    $resp->CDN_B2 = '';
}

// Update FTP CDN
if (AVideoPlugin::isEnabledByName('FTP_Storage')) {
    $resp->CDN_FTP = CDN::getCDN_FTPURL();
} else {
    $resp->CDN_FTP = '';
}

// Update Live CDN
$resp->CDN_Live = '';
$resp->CDN_LiveServers = [];
$plugin = AVideoPlugin::getDataObjectIfEnabled('Live');
if (!empty($plugin)) {
    if ($plugin->useLiveServers) {
        $rows = Live_servers::getAllActive();
        foreach ($rows as $value) {
            if (empty($value['playerServer'])) {
                continue;
            }
            $resp->CDN_LiveServers[] = [
                'id' => $value['id'],
                'url' => addLastSlash($value['playerServer']),
            ];
        }
    } else {
        $resp->CDN_Live = addLastSlash($plugin->playerServer);
    }
}


// Update YPT Storage CDN
$resp->CDN_YPTStorage = [];
$plugin = AVideoPlugin::getDataObjectIfEnabled('YPTStorage');
if (!empty($plugin)) {
    $rows = Sites::getAllActive();
    foreach ($rows as $value) {
        if (empty($value['url'])) {
            continue;
        }
        $resp->CDN_YPTStorage[] = [
            'id' => $value['id'],
            'url' => addLastSlash($value['url']),
        ];
    }
}

// Update Liveservers CDN
$cdn = AVideoPlugin::loadPluginIfEnabled('CDN');
$id = $cdn->setDataObject($obj);
if (!empty($id)) {
    $resp->error = false;
}

die(json_encode($resp));
