<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

header('Content-Type: application/json');

// This mutates video status; must not be reachable by GET (<img>/navigation CSRF).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(_json_encode(['error' => true, 'msg' => 'Method not allowed']));
}

if (empty($_POST['videos_id'])) {
    forbiddenPage('videos_id is empty');
}

if (!isGlobalTokenValid()) {
    http_response_code(403);
    die(_json_encode(['error' => true, 'msg' => 'Invalid or missing CSRF token']));
}

AVideoPlugin::loadPlugin('Scheduler');

$obj = new stdClass();
$obj->msg = '';
$obj->videos_id = $_POST['videos_id'];
$obj->released = Scheduler::releaseVideosNow($obj->videos_id);
$obj->error = empty($obj->released);
if ($obj->released) {
    $video = new Video('', '', $obj->videos_id);
    $obj->msg = __('Video released') . ': ' . $video->getTitle();
} else {
    $obj->msg = __('Error on release video');
}


die(_json_encode($obj));
