<?php
require_once '../videos/configuration.php';
require_once 'video.php';

$obj = new stdClass();
$obj->success = false;

require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'google/autoload.php';
header('Content-Type: application/json');

$OAUTH2_CLIENT_ID = $config->getAuthGoogle_id();
$OAUTH2_CLIENT_SECRET = $config->getAuthGoogle_key();

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
$redirectUri = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],FILTER_SANITIZE_URL);
$redirect = "{$global['webSiteRootURL']}mvideos";
$client->setRedirectUri($redirectUri);
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
  header('Location: ' . $redirect);
}
$v = new Video("", "", $_POST['id']);
if(!$v->userCanManageVideo()){
    $obj->msg = __("You can not Manage This Video");
    die(json_encode($obj));
}
if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}
// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  try{
    // REPLACE this value with the path to the file you are uploading.
    $videoPath = $v->getExistingVideoFile();
    // Create a snippet with title, description, tags and category ID
    // Create an asset resource and set its snippet metadata and type.
    // This example sets the video's title, description, keyword tags, and
    // video category.
    $snippet = new Google_Service_YouTube_VideoSnippet();
    $snippet->setTitle($v->getTitle());
    $snippet->setDescription($v->getDescription());
    $snippet->setTags(array("YouPHPTube", $config->getWebSiteTitle()));
    // Numeric video category. See
    // https://developers.google.com/youtube/v3/docs/videoCategories/list
    // $snippet->setCategoryId("22");
    // Set the video's status to "public". Valid statuses are "public",
    // "private" and "unlisted".
    $status = new Google_Service_YouTube_VideoStatus();
    $status->privacyStatus = "public";
    // Associate the snippet and status objects with a new video resource.
    $video = new Google_Service_YouTube_Video();
    $video->setSnippet($snippet);
    $video->setStatus($status);
    // Specify the size of each chunk of data, in bytes. Set a higher value for
    // reliable connection as fewer chunks lead to faster uploads. Set a lower
    // value for better recovery on less reliable connections.
    $chunkSizeBytes = 1 * 1024 * 1024;
    // Setting the defer flag to true tells the client to return a request which can be called
    // with ->execute(); instead of making the API call immediately.
    $client->setDefer(true);
    // Create a request for the API's videos.insert method to create and upload the video.
    $insertRequest = $youtube->videos->insert("status,snippet", $video);
    // Create a MediaFileUpload object for resumable uploads.
    $media = new Google_Http_MediaFileUpload(
        $client,
        $insertRequest,
        'video/*',
        null,
        true,
        $chunkSizeBytes
    );
    $media->setFileSize(filesize($videoPath));
    // Read the media file and upload it chunk by chunk.
    $status = false;
    $handle = fopen($videoPath, "rb");
    while (!$status && !feof($handle)) {
      $chunk = fread($handle, $chunkSizeBytes);
      $status = $media->nextChunk($chunk);
    }
    fclose($handle);
    // If you want to make other calls after the file upload, set setDefer back to false
    $client->setDefer(false);
    $obj->success = true;
    $obj->title = $status['snippet']['title'];
    $obj->id = $status['id'];
    $obj->status = $status;
    $obj->msg = sprintf(__("Your video <a href='https://youtu.be/%s' target='_blank' class='btn btn-default'><span class='fa fa-youtube-play'></span> %s</a> was uploaded to your <a href='https://www.youtube.com/my_videos' class='btn btn-default' target='_blank'><span class='fa fa-youtube'></span> YouTube Account</a><br> "), $obj->id, $obj->title);
    $v->setYoutubeId($obj->id);
    $v->save();
    
  } catch (Google_Service_Exception $e) {
    $obj->msg = sprintf(__("A service error occurred: %s"), $e->getMessage());
  } catch (Google_Exception $e) {
    $obj->msg = sprintf(__("An client error occurred: %s"), $e->getMessage());
  }
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
    $obj->msg = "<h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>";
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;
  $authUrl = $client->createAuthUrl();
  $obj->msg = "<h3>Authorization Required</h3><p>You need to <a href=\"{$authUrl}\"  class='btn btn-danger'><span class='fa fa-youtube'></span> authorize access</a> before proceeding.<p>";
  
}
echo json_encode($obj);
?>