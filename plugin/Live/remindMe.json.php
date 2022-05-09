<?php

require_once dirname(__FILE__) . '/../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['live_schedule_id'])) {
    forbiddenPage('live_schedule_id cannot be empty');
}

if (empty($_REQUEST['minutesEarlier'])) {
    $_REQUEST['minutesEarlier'] = 10;
}

$reminder = Live::setLiveScheduleReminder($_REQUEST['live_schedule_id'], $_REQUEST['minutesEarlier'], @$_REQUEST['deleteIfExists']);

$obj = new stdClass();
$obj->minutesEarlier = $_REQUEST['minutesEarlier'];
$obj->live_schedule_id = $_REQUEST['live_schedule_id'];
$obj->msg = '';
$obj->isActive = false;

if ($reminder->error) {
    forbiddenPage($reminder->msg);
} else {
    if ($reminder->deleted) {
        $obj->msg = __('Reminder deleted');
        $obj->warning = 1;
    }else{
        $obj->msg = __('Reminder added');
        $obj->isActive = true;
    }
}

$obj->response = $reminder;
$obj->error = false;

die(_json_encode($obj));
