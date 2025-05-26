<?php
header('Content-Type: application/json');

if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
$obj = new stdClass();
$obj->error = true;
$obj->msg = "";
$obj->meet_schedule_id = 0;

if (empty($objM)) {
    $obj->msg = "Plugin disabled";
    die(json_encode($obj));
}

//User::loginFromRequest();

if (!User::canCreateMeet()) {
    $obj->msg = "You Cannot create meet";
    _error_log('Meet:User::canCreateMeet error Required: ' . json_encode($_REQUEST));
    _error_log('Meet:User::canCreateMeet error POST: ' . json_encode($_POST));
    _error_log("Meet:User::canCreateMeet error GET: " . json_encode($_GET));
    _error_log("Meet:User::canCreateMeet error php://input" . file_get_contents("php://input"));
    die(json_encode($obj));
}

if (!empty($_REQUEST['id'])) {
    $obj->meet_schedule_id = intval($_REQUEST['id']);
    if (!Meet::canManageSchedule($obj->meet_schedule_id)) {
        $obj->msg = "You Cannot edit this schedule";
        die(json_encode($obj));
    }
}
if (empty($obj->meet_schedule_id) && !empty($_REQUEST['starts'])) {
    $date_now = time();
    $date2 = strtotime($_REQUEST['starts']);
    if ($date_now > $date2) {
        $obj->msg = "You cannot save meetings for a past time";
        die(json_encode($obj));
    }
}
$obj->roomName = Meet::createRoomName(@$_REQUEST['RoomTopic']);

if (empty($_REQUEST['starts'])) {
    $_REQUEST['starts'] = date("Y-m-d H:i:s");
}

if (empty($_REQUEST['status'])) {
    $_REQUEST['status'] = 'a';
}
if (!isset($_REQUEST['public'])) {
    $_REQUEST['public'] = 2;
}
if (!isset($_REQUEST['live_stream'])) {
    $_REQUEST['live_stream'] = 0;
}

if (empty($_REQUEST['userGroups']) || !empty($_REQUEST['public'])) {
    $_REQUEST['userGroups'] = [];
}

$o = new Meet_schedule($obj->meet_schedule_id);
$o->setUsers_id(User::getId());
$o->setStatus($_REQUEST['status']);
$o->setPublic($_REQUEST['public']);
$o->setLive_stream($_REQUEST['live_stream']);
$o->setPassword(@$_REQUEST['RoomPasswordNew']);
$o->setTopic(@$_REQUEST['RoomTopic']);
$o->setStarts($_REQUEST['starts']);
$o->setName($obj->roomName);
$o->setMeet_code(_uniqid());
$meet_schedule_id = $o->save();
if ($meet_schedule_id) {
    Meet_schedule_has_users_groups::saveUsergroupsToMeet($meet_schedule_id, $_REQUEST['userGroups']);
    $createdMeet = new Meet_schedule($meet_schedule_id);
    $obj->RoomPassword = $createdMeet->getPassword();
}
$obj->meet_schedule_id = $meet_schedule_id;
$obj->password = @$_REQUEST['RoomPasswordNew'];
$obj->error = empty($meet_schedule_id);

if ($obj->error) {
    // check if the user install the tables
    if (!Meet_schedule::isTableInstalled()) {
        $obj->msg = "You need to install the tables for this plugin";
    }
}

$obj->link = Meet::getMeetLink($meet_schedule_id);
$obj->shortLink = Meet::getMeetShortLink($meet_schedule_id);
$obj->jwt = Meet::getToken($meet_schedule_id);
$obj->domain = Meet::getDomainURL();
$obj->joinURL = Meet::getJoinURL();
$obj->roomID = Meet::getRoomID($meet_schedule_id);
$obj->invitation = Meet::getInvitation($meet_schedule_id);
$obj->iframeURL = Meet::getIframeURL($meet_schedule_id);
$obj->linkAndPassword = addQueryStringParameter($obj->link, 'meet_password', $obj->password);

$obj->RTMPLink = false;
$obj->LinkToLive = false;

if(AVideoPlugin::isEnabledByName('Live')){
    $obj->RTMPLink = Live::getRTMPLink(User::getId());
    $obj->LinkToLive = Live::getLinkToLiveFromUsers_id(User::getId());
    $obj->LinkToLiveEmbed = addQueryStringParameter($obj->LinkToLive, 'embed', 1);
}

//var_dump($obj->domain);

die(json_encode($obj));
