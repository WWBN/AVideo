<?php

require_once '../../../videos/configuration.php';

header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

$objP = AVideoPlugin::getDataObjectIfEnabled('Live');
if (empty($objP)) {
    $obj->msg = __('Live plugin is disabled');
    die(json_encode($obj));
}

if (!User::canStream()) {
    $obj->msg = __('Cannot stream');
    die(json_encode($obj));
}
$users_id = User::getId();
$lives = LiveTransmitionHistory::getAllActiveFromUser($users_id);

if(empty($lives)){
    $lives = LiveTransmitionHistory::getAllFromUser($users_id, false, false, 1);
}

$restreamers = Live_restreams::getAllFromUser($users_id);

foreach ($lives as $key => $value) {
    $lives[$key]['restream'] = array();
    foreach ($restreamers as $restream) {
        $log = Live_restreams_logs::getLatest($value['id'], $restream['id']);
        if(empty($log)){
            $log = array();
        }
        $restream['log'] = $log;
        
        foreach ($log as $log_key => $log_value) {
            $restream['log_'.$log_key] = $log_value;
        }
        
        $restream['log_json'] = json_decode($restream['log_json'], true);
        $restream['live_url'] = !empty($restream['log_json']['live_url'][$restream['id']])?$restream['log_json']['live_url'][$restream['id']]:'';

        $lives[$key]['restream'][] = $restream;
    }
}

$obj->error = false;
$obj->lives = $lives;

die(json_encode($obj));
