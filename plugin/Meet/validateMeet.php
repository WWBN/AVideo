<?php

$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($objM)) {
    die("Plugin disabled");
}

$meet_schedule_id = intval($_GET['meet_schedule_id']);

if (empty($meet_schedule_id)) {
    forbiddenPage("meet schedule id cannot be empty");
}

$meet = new Meet_schedule($meet_schedule_id);
if(empty($meet->getName())){
    forbiddenPage("meet not found");
}

$userCredentials = User::loginFromRequestToGet();

$meetDomain = Meet::getDomain();
if (empty($meetDomain)) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/?error=The Server is Not ready");
    exit;
}

$canJoin = Meet::canJoinMeetWithReason($meet_schedule_id);
if (!$canJoin->canJoin) {
    header("Location: {$global['webSiteRootURL']}plugin/Meet/?error=" . urlencode($canJoin->reason));
    exit;
}

if (empty($meet->getPublic()) && !User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user?redirectUri=" . urlencode($meet->getMeetLink()) . "&msg=" . urlencode(__("Please, login before join a meeting")));
    exit;
}
