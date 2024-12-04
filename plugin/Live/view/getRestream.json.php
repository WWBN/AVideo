<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->start = microtime(true);
$obj->lastTime = microtime(true);
$obj->debug = array();
debugLog(__LINE__);
$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

debugLog(__LINE__);
if (!User::canStream()) {
    $obj->msg = __('Cannot stream');
    die(json_encode($obj));
}

debugLog(__LINE__);
$obj->live_transmitions_history_id = intval($_REQUEST['live_transmitions_history_id']);
$obj->restreams_id = intval($_REQUEST['restreams_id']);

debugLog(__LINE__);
if (empty($obj->live_transmitions_history_id)) {
    forbiddenPage('live_transmitions_history_id is empty');
}
if (empty($obj->restreams_id)) {
    forbiddenPage('restreams_id is empty');
}

debugLog(__LINE__);

$obj->url = Live_restreams_logs::getURLFromTransmitionAndRestream($obj->live_transmitions_history_id, $obj->restreams_id, 'log');

debugLog(__LINE__);
if (empty($obj->url)) {
    $obj->error = false;
    $obj->log = false;
} else {
    debugLog(__LINE__);
    $content = url_get_contents($obj->url);
    debugLog(__LINE__);
    $obj->log = json_decode($content);
    debugLog(__LINE__);
    if (!empty($obj->log)) {
        $obj->error = false;
    }else{
        _error_log("Restream URL content error {$obj->url} {$content}");
    }
}

debugLog(__LINE__);
//unset($obj->url);

$obj->end = number_format(microtime(true) - $obj->start, 2);
die(json_encode($obj));


function debugLog($line){
    global $obj;
    $debug = array('time'=>microtime(true), 'line'=>$line, 'takes'=>number_format(microtime(true)-$obj->lastTime, 2) );    
    $debug['takesTooLong'] =  $debug['takes']>0.5;
    $obj->debug[] = $debug;
    $obj->lastTime = microtime(true);
}