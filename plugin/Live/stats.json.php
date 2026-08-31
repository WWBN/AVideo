<?php
//exit;
header('Content-Type: application/json');
require_once '../../videos/configuration.php';

/*
  if(!requestComesFromSafePlace()) {
  _error_log("Why are you requesting this ".getSelfURI()." ".json_encode($_SERVER));
  die();
  }
 *
 */

ini_set('max_execution_time', 10);
set_time_limit(10);
// read-only endpoint, polled frequently; release the session lock early so it
// doesn't queue up behind/behind other requests from the same browser session
_session_write_close();
$pobj = AVideoPlugin::getDataObjectIfEnabled("Live");
if (empty($pobj->server_type->value)) {
    ini_set('max_execution_time', 180);
    set_time_limit(180);
}
if (empty($pobj)) {
    die(json_encode("Plugin disabled"));
}

$timeName = "stats.json.php";
TimeLogStart($timeName);
$json = getStatsNotifications();
//var_dump($json);exit;
TimeLogEnd($timeName, __LINE__);
$json = object_to_array($json);
TimeLogEnd($timeName, __LINE__);

if (!empty($_REQUEST['name'])) {
    TimeLogEnd($timeName, __LINE__);
    $json['msg'] = 'OFFLINE';
    $json['name'] = $_REQUEST['name'];
    if (!empty($json['applications'])) {
        foreach ($json['applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
    TimeLogEnd($timeName, __LINE__);
    if (!empty($json['hidden_applications'])) {
        foreach ($json['hidden_applications'] as $value) {
            if (!empty($value['key']) && $value['key'] == $_REQUEST['name']) {
                $json['msg'] = 'ONLINE';
                break;
            }
        }
    }
    TimeLogEnd($timeName, __LINE__);
}

// hidden_applications is an internal working list of streams the current (possibly anonymous)
// requester isn't authorized to see - no known JS consumer reads it from this endpoint, so it must
// not be serialised at all (title/channel/masked-key metadata is still a disclosure otherwise).
unset($json['hidden_applications']);

// applications are publicly listed (unlike hidden_applications above), but a password-protected
// transmission still requires Live::passwordIsGood() before it can actually be watched - strip the
// raw key/m3u8 here too unless the caller already satisfied that check (or owns/administers it).
if (!empty($json['applications'])) {
    foreach ($json['applications'] as &$app) {
        if (is_array($app) && !empty($app['isPasswordProtected']) && !empty($app['key'])) {
            $isOwnStream = User::isLogged() && !empty($app['users_id']) && ((int) $app['users_id'] === (int) User::getId());
            if (!User::isAdmin() && !$isOwnStream && !Live::passwordIsGood($app['key'])) {
                unset($app['key'], $app['m3u8']);
            }
        }
    }
    unset($app);
}
//var_dump($json);exit;
echo json_encode($json);
