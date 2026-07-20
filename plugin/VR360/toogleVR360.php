<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
header('Content-Type: application/json');

if (!User::isLogged()) {
	forbiddenPage('Login required');
}

$response = array('error' => false, 'msg' => '', 'active' => false);

try {
	$videos_id = intval($_POST['videos_id']);
	$active = isset($_POST['vr360']) ? intval(!empty($_POST['vr360'])) : null;

	if (empty($videos_id)) {
		throw new Exception('Invalid video ID');
	}
	if (!Video::canEdit($videos_id)) {
		forbiddenPage('Permission denied', true);
	}
	if (!isGlobalTokenValid()) {
		forbiddenPage('Invalid or missing CSRF token', true);
	}

	$saved = VideosVR360::toogleVR360($videos_id, $active);
	if ($saved === false) {
		throw new Exception('Could not save VR360 status');
	}

	$response['active'] = intval(!empty($saved));
	$response['msg'] = 'VR360 status saved';
} catch (\Throwable $th) {
	$response['error'] = true;
	$response['msg'] = 'Could not save VR360 status';
	_error_log('VR360::toogleVR360 endpoint error: ' . $th->getMessage(), AVideoLog::$ERROR);
}

echo json_encode($response);
