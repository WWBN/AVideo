<?php
// YouTube categories for the publish form, read with the logged user's YouTube connection
require_once __DIR__ . '/../../videos/configuration.php';

header('Content-Type: application/json');

$plugin = AVideoPlugin::loadPluginIfEnabled('SocialMediaPublisher');
if (empty($plugin)) {
    forbiddenPage('SocialMediaPublisher is disabled');
}
if (!User::isLogged()) {
    forbiddenPage('You must login');
}
$row = Publisher_user_preferences::getFromDb(intval(@$_REQUEST['id']));
if (empty($row) || User::getId() != $row['users_id']) {
    forbiddenPage('Publisher_user_preferences does not belong to your user');
}

$obj = array('error' => true, 'msg' => '', 'categories' => array());
try {
    $pref = new Publisher_user_preferences($row['id']);
    if (!YouTubePublisher::isYouTubeProvider($pref->getProviderName())) {
        throw new Exception('not a YouTube connection');
    }
    $obj['categories'] = YouTubePublisher::getCategories($row['id'], getLanguage());
    $obj['error'] = false;
} catch (YouTubePublisherException $e) {
    _error_log('youtubeCategories.json.php ' . $e->getErrorCode() . ' ' . $e->getMessage(), AVideoLog::$WARNING);
    $obj['msg'] = YouTubePublisher::getMessage($e->getErrorCode());
} catch (\Throwable $th) {
    $parsed = YouTubePublisher::parseGoogleError($th);
    _error_log('youtubeCategories.json.php ' . get_class($th) . " HTTP {$parsed['httpCode']} {$parsed['reason']}", AVideoLog::$WARNING);
    $obj['msg'] = YouTubePublisher::getMessage(YouTubePublisher::classifyGoogleError($parsed['httpCode'], $parsed['reason']));
}

echo json_encode($obj);
