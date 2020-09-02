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

if (!User::canCreateMeet()) {
    $obj->msg = "You Cannot create meet";
    die(json_encode($obj));
}

if (!empty($_REQUEST['id'])) {
    $obj->meet_schedule_id = intval($_REQUEST['id']);
    if (!Meet::canManageSchedule($obj->meet_schedule_id)) {
        $obj->msg = "You Cannot edit this schedule";
        die(json_encode($obj));
    }
}
if (empty($obj->meet_schedule_id) && !empty($_POST['starts'])) {
    $date_now = time();
    $date2 = strtotime($_POST['starts']);
    if ($date_now > $date2) {
        $obj->msg = "You cannot save meetings for a past time";
        die(json_encode($obj));
    }
}
$obj->roomName = Meet::createRoomName(@$_POST['RoomTopic']);


if (empty($_POST['starts'])) {
    $_POST['starts'] = date("Y-m-d H:i:s");
}

if (empty($_POST['status'])) {
    $_POST['status'] = 'a';
}
if (!isset($_POST['public'])) {
    $_POST['public'] = 1;
}
if (!isset($_POST['live_stream'])) {
    $_POST['live_stream'] = 0;
}

if (empty($_POST['userGroups']) || !empty($_POST['public'])) {
    $_POST['userGroups'] = array();
}

$o = new Meet_schedule($obj->meet_schedule_id);
$o->setUsers_id(User::getId());
$o->setStatus($_POST['status']);
$o->setPublic($_POST['public']);
$o->setLive_stream($_POST['live_stream']);
$o->setPassword(@$_POST['RoomPasswordNew']);
$o->setTopic(@$_POST['RoomTopic']);
$o->setStarts($_POST['starts']);
$o->setName($obj->roomName);
$o->setMeet_code(uniqid());
$meet_schedule_id = $o->save();
if ($meet_schedule_id) {
    Meet_schedule_has_users_groups::saveUsergroupsToMeet($meet_schedule_id, $_POST['userGroups']);
}

$obj->password = @$_POST['RoomPasswordNew'];
$obj->error = empty($meet_schedule_id);
$obj->link = Meet::getMeetLink($meet_schedule_id);
$obj->jwt = Meet::getToken($meet_schedule_id);;
        
        
die(json_encode($obj));
?>