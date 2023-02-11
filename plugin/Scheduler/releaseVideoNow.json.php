<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

header('Content-Type: application/json');

if (empty($_REQUEST['videos_id'])) {
    forbiddenPage('videos_id is empty');
}

AVideoPlugin::loadPlugin('Scheduler');

$obj = new stdClass();
$obj->msg = '';
$obj->videos_id = $_REQUEST['videos_id'];
$obj->released = Scheduler::releaseVideosNow($obj->videos_id);
$obj->error = empty($obj->released);
if ($obj->released) {
    $video = new Video('', '', $obj->videos_id);
    $obj->msg = __('Video released') . ': ' . $video->getTitle();
} else {
    $obj->msg = __('Error on release video');
}


die(_json_encode($obj));
