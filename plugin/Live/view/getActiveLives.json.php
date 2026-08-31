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

// SECURITY: do NOT pass users_id=0 for admins here - LiveTransmitionHistory::getAllFromUser()
// treats 0 as "skip the owner filter entirely", which returned literally every account's
// active live to any isAdmin=1 user. On a multi-tenant install (many unrelated
// clients/channels, some of them flagged isAdmin so they can self-manage their own
// account) this leaked other customers' live streams - title, key, and the restream
// panel's "Open" link straight to their real YouTube/Facebook broadcast - to any admin,
// not just platform-wide support staff. Fixed by always passing the browsing user's own
// id: getAllFromUser()/getAllActiveFromUser() already scope by
// "(users_id = X OR users_id_company = X)" for any non-zero id, so an admin still sees
// a live streaming under an explicitly linked company sub-account (the original use case
// this admin-sees-all behavior was added for, see avideo-live-restream-wrong-user-visibility
// repo memory), just not every unrelated account on the install.
$liveLookupUsersId = $users_id;
$lives = LiveTransmitionHistory::getAllActiveFromUser($liveLookupUsersId);

if(empty($lives)){
    $lives = LiveTransmitionHistory::getAllFromUser($liveLookupUsersId, false, false, 1);
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
