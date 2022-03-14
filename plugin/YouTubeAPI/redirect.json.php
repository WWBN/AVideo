<?php
error_reporting(0);
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'plugin/YouTubeAPI/Objects/YouTubeUploads.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';
header('Content-Type: application/json');
$object = new stdClass();
        $object->error = true;
        $object->msg = "";
        $object->url = self::getUploadedURL($videos_id);
        $object->databaseSaved = false;
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$youTubeObj = AVideoPlugin::getObjectData("YouTubeAPI");

$client = new Google_Client();
$client->setClientId($youTubeObj->client_id);
$client->setClientSecret($youTubeObj->client_secret);
$client->setScopes('https://www.googleapis.com/auth/youtube');
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);
// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }
    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    $object->error = false;
}
echo json_encode($obj);
