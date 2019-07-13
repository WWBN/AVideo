<?php

error_reporting(0);
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
$obj2 = new stdClass();
$obj2->error = true;
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'google/autoload.php';
header('Content-Type: application/json');
$_GET['maxResults'] = 24;
if(empty($_GET['q']) && !empty($_POST['q'])){
    $_GET['q'] = $_POST['q'];
}else{
   $_GET['q'] = "YouPHPTube";
}
$obj = YouPHPTubePlugin::getObjectData("BulkEmbed");
$OAUTH2_CLIENT_ID = $obj->Google_Client_ID;
$OAUTH2_CLIENT_SECRET = $obj->Google_Client_secret;
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
//$redirectUri = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],FILTER_SANITIZE_URL);
$redirectUri = "{$global['webSiteRootURL']}plugin/BulkEmbed/youtubeSearch.json.php";
$client->setRedirectUri($redirectUri);
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
    if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
    }
    $client->authenticate($_GET['code']);
    $_SESSION[$tokenSessionKey] = $client->getAccessToken();
    header("Location: {$global['webSiteRootURL']}plugin/BulkEmbed/search.php");
}
if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
}

if ($client->getAccessToken()) {
    try {
        // Call the search.list method to retrieve results matching the specified
        // query term.
        $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $_GET['q'],
            'maxResults' => $_GET['maxResults'],
            'type' => 'video',
            'videoEmbeddable' => 'true'
        ));
        $obj2->error = false;
        $obj2->response = $searchResponse;
    } catch (Google_Service_Exception $e) {
        $obj2->msg = sprintf('<p>A service error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    } catch (Google_Exception $e) {
        $obj2->msg = sprintf('<p>An client error occurred: <code>%s</code></p>', htmlspecialchars($e->getMessage()));
    }
} else {
    // If the user hasn't authorized the app, initiate the OAuth flow
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $authUrl = $client->createAuthUrl();
    $obj2->authUrl =  $authUrl;
    $obj2->msg = "<h3>Authorization Required</h3><p>You need to <a href=\"{$authUrl}\"  class='btn btn-danger'><span class='fab fa-youtube-square'></span> authorize access</a> before proceeding.<p>";
}
echo json_encode($obj2);
