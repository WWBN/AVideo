<?php
require_once 'objects/simple-php-router/vendor/autoload.php';
$basePath = parse_url ($global['webSiteRootURL'], PHP_URL_PATH);
use Pecee\SimpleRouter\SimpleRouter;
SimpleRouter::get($basePath, function() {
    require_once "view/index.php"; exit;
});
SimpleRouter::get($basePath."info", function() {
    require_once "view/info.php"; exit;
});
/*SimpleRouter::post($basePath."subscribes.json", function() {
    require_once "objects/subscribe.json.php";
});*/
SimpleRouter::get($basePath."siteConfigurations", function() {
    require_once "view/configurations.php"; exit;
});
SimpleRouter::get($basePath."signUp", function() {
    require_once "view/signUp.php"; exit;
});
SimpleRouter::get($basePath."categories", function() {
    require_once "view/managerCategories.php"; exit;
});
SimpleRouter::get($basePath."about", function() {
    require_once "view/about.php"; exit;
});
SimpleRouter::get($basePath."orphanFiles", function() {
    require_once "view/orphanFiles.php"; exit;
});
SimpleRouter::get($basePath."contact", function() {
    require_once "view/contact.php"; exit;
});
SimpleRouter::get($basePath."user", function() {
    require_once "view/user.php"; exit;
});
SimpleRouter::get($basePath."users", function() {
    require_once "view/managerUsers.php"; exit;
});
SimpleRouter::get($basePath."usersGroups", function() {
    require_once "view/managerUsersGroups.php"; exit;
});
SimpleRouter::get($basePath."mvideos", function() {
    require_once "view/managerVideos.php"; exit;
});
SimpleRouter::get($basePath."videosAndroid.json", function() {
    require_once "objects/videosAndroid.json.php"; exit;
});
SimpleRouter::get($basePath."videoAndroid.json", function() {
    require_once "objects/videoAndroid.json.php"; exit;
});
SimpleRouter::get($basePath."captcha", function() {
    require_once "objects/getCaptcha.php"; exit;
});
SimpleRouter::get($basePath."logoff", function() {
    require_once "objects/logoff.php"; exit;
});
SimpleRouter::get($basePath."charts", function() {
    require_once "view/charts.php"; exit;
});
SimpleRouter::get($basePath."update", function() {
    require_once "view/update.php"; exit;
});
SimpleRouter::post($basePath."googleAdView", function() {
    require_once "view/googleAdView.php"; exit;
});
SimpleRouter::post($basePath."notifications.json", function() {
    require_once "objects/notifications.json.php"; exit;
});
SimpleRouter::get($basePath."notifySubscribers.json", function() {
    require_once "objects/notifySubscribers.json.php"; exit;
});
SimpleRouter::get($basePath."subscribes", function() {
    require_once "view/managerSubscribes.php"; exit;
});
SimpleRouter::post($basePath."videosList", function() {
    require_once "view/videosList.php"; exit;
});
SimpleRouter::post($basePath."getDownloadProgress", function() {
    require_once "objects/downloadVideoProgress.php"; exit;
});
SimpleRouter::post($basePath."downloadNow", function() {
    require_once "objects/downloadVideo.php"; exit;
});
SimpleRouter::get($basePath."comments", function() {
    require_once "view/managerComments.php"; exit;
});
SimpleRouter::get($basePath."videoOnly", function() {
    $_GET['type'] = "video";
    require_once "view/index.php"; exit;
});
SimpleRouter::get($basePath."audioOnly", function() {
  $_GET['type'] = "audio";
  require_once "view/index.php"; exit;
});
SimpleRouter::post($basePath."comments.json/{videoId}", function($videoId) {
    $_GET['video_id'] = $videoId;
    require_once "objects/comments.json.php"; exit;
});
// eventualy incomplete
SimpleRouter::post($basePath."status", function() {
    require_once "objects/status.json.php"; exit;
});
SimpleRouter::get($basePath."plugins", function() {
    require_once "view/managerPlugins.php"; exit;
});
SimpleRouter::post($basePath."youPHPTubeQueueEncoder.json", function() {
    require_once "objects/youPHPTubeQueueEncoder.json.php"; exit;
});
SimpleRouter::post($basePath."youPHPTubeEncoder.json", function() {
    require_once "objects/youPHPTubeEncoder.json.php"; exit;
});
SimpleRouter::get($basePath."youPHPTubeQueueEncoder.json", function() {
    require_once "objects/youPHPTubeQueueEncoder.json.php"; exit;
});
SimpleRouter::get($basePath."youPHPTubeEncoder.json", function() {
    require_once "objects/youPHPTubeEncoder.json.php"; exit;
});
SimpleRouter::post($basePath."plugins.json", function() {
    require_once "objects/plugins.json.php"; exit;
});
SimpleRouter::get($basePath."channels", function() {
    require_once "view/channels.php"; exit;
});
SimpleRouter::get($basePath."help", function() {
    require_once "view/help.php"; exit;
});
SimpleRouter::post($basePath."like", function() {
    $_GET['like']="1";
    require_once "objects/like.json.php"; exit;
});
SimpleRouter::post($basePath."dislike", function() {
    $_GET['like']="-1";
    require_once "objects/like.json.php"; exit;
});
SimpleRouter::get($basePath."channel/{channelName?}", function ($channelName = '') {
    $_GET['channelName'] = $channelName;
    require_once 'view/channel.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."cat/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."upload", function () {
    require_once 'view/mini-upload-form/index.php'; exit;
});
SimpleRouter::get($basePath."cat/{catName}/video/{videoName?}", function ($catName,$videoName = '') {
    $_GET['catName'] = $catName;
    $_GET['videoName'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);

SimpleRouter::get($basePath."cat/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."video/{videoName?}", function ($videoName = '') {
    $_GET['videoName'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."v/{videoName?}", function ($videoName = '') {
    $_GET['v'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
// if it's used external, by encoder or so
SimpleRouter::post($basePath."login", function() {
    require_once "objects/login.json.php"; exit;
});
SimpleRouter::get($basePath."login", function() {
    require_once "objects/login.json.php"; exit;
});


// Translated!

SimpleRouter::get($basePath.__("info"), function() {
    require_once "view/info.php"; exit;
});
/*SimpleRouter::post($basePath."subscribes.json", function() {
    require_once "objects/subscribe.json.php";
});*/
SimpleRouter::get($basePath.__("siteConfigurations"), function() {
    require_once "view/configurations.php"; exit;
});
SimpleRouter::get($basePath.__("signUp"), function() {
    require_once "view/signUp.php"; exit;
});
SimpleRouter::get($basePath.__("categories"), function() {
    require_once "view/managerCategories.php"; exit;
});
SimpleRouter::get($basePath.__("about"), function() {
    require_once "view/about.php"; exit;
});
SimpleRouter::get($basePath.__("orphanFiles"), function() {
    require_once "view/orphanFiles.php"; exit;
});
SimpleRouter::get($basePath.__("contact"), function() {
    require_once "view/contact.php"; exit;
});
SimpleRouter::get($basePath.__("user"), function() {
    require_once "view/user.php"; exit;
});
SimpleRouter::get($basePath.__("users"), function() {
    require_once "view/managerUsers.php"; exit;
});
SimpleRouter::get($basePath.__("usersGroups"), function() {
    require_once "view/managerUsersGroups.php"; exit;
});
SimpleRouter::get($basePath.__("mvideos"), function() {
    require_once "view/managerVideos.php"; exit;
});
SimpleRouter::get($basePath."videosAndroid.json", function() {
    require_once "objects/videosAndroid.json.php"; exit;
});
SimpleRouter::get($basePath."videoAndroid.json", function() {
    require_once "objects/videoAndroid.json.php"; exit;
});
SimpleRouter::get($basePath."captcha", function() {
    require_once "objects/getCaptcha.php"; exit;
});
SimpleRouter::get($basePath.__("logoff"), function() {
    require_once "objects/logoff.php"; exit;
});
SimpleRouter::get($basePath.__("charts"), function() {
    require_once "view/charts.php"; exit;
});
SimpleRouter::get($basePath.__("update"), function() {
    require_once "view/update.php"; exit;
});
SimpleRouter::post($basePath."googleAdView", function() {
    require_once "view/googleAdView.php"; exit;
});
SimpleRouter::post($basePath."notifications.json", function() {
    require_once "objects/notifications.json.php"; exit;
});
SimpleRouter::get($basePath."notifySubscribers.json", function() {
    require_once "objects/notifySubscribers.json.php"; exit;
});
SimpleRouter::get($basePath.__("subscribes"), function() {
    require_once "view/managerSubscribes.php"; exit;
});
SimpleRouter::post($basePath.__("videosList"), function() {
    require_once "view/videosList.php"; exit;
});
SimpleRouter::post($basePath."getDownloadProgress", function() {
    require_once "objects/downloadVideoProgress.php"; exit;
});
SimpleRouter::post($basePath."downloadNow", function() {
    require_once "objects/downloadVideo.php"; exit;
});
SimpleRouter::get($basePath."comments", function() {
    require_once "view/managerComments.php"; exit;
});
SimpleRouter::get($basePath.__("videoOnly"), function() {
    $_GET['type'] = "video";
    require_once "view/index.php"; exit;
});
SimpleRouter::get($basePath.__("audioOnly"), function() {
  $_GET['type'] = "audio";
  require_once "view/index.php"; exit;
});
SimpleRouter::post($basePath."comments.json/{videoId}", function($videoId) {
    $_GET['video_id'] = $videoId;
    require_once "objects/comments.json.php"; exit;
});
// eventualy incomplete
SimpleRouter::post($basePath."status", function() {
    require_once "objects/status.json.php"; exit;
});
SimpleRouter::get($basePath."plugins", function() {
    require_once "view/managerPlugins.php"; exit;
});
SimpleRouter::post($basePath."youPHPTubeQueueEncoder.json", function() {
    require_once "objects/youPHPTubeQueueEncoder.json.php"; exit;
});
SimpleRouter::post($basePath."youPHPTubeEncoder.json", function() {
    require_once "objects/youPHPTubeEncoder.json.php"; exit;
});
SimpleRouter::get($basePath."youPHPTubeQueueEncoder.json", function() {
    require_once "objects/youPHPTubeQueueEncoder.json.php"; exit;
});
SimpleRouter::get($basePath."youPHPTubeEncoder.json", function() {
    require_once "objects/youPHPTubeEncoder.json.php"; exit;
});
SimpleRouter::post($basePath."plugins.json", function() {
    require_once "objects/plugins.json.php"; exit;
});
SimpleRouter::get($basePath."channels", function() {
    require_once "view/channels.php"; exit;
});
SimpleRouter::get($basePath.__("help"), function() {
    require_once "view/help.php"; exit;
});
SimpleRouter::post($basePath."like", function() {
    $_GET['like']="1";
    require_once "objects/like.json.php"; exit;
});
SimpleRouter::post($basePath."dislike", function() {
    $_GET['like']="-1";
    require_once "objects/like.json.php"; exit;
});
SimpleRouter::get($basePath.__("channel")."/{channelName?}", function ($channelName = '') {
    $_GET['channelName'] = $channelName;
    require_once 'view/channel.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath.__("cat")."/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."upload", function () {
    require_once 'view/mini-upload-form/index.php'; exit;
});
SimpleRouter::get($basePath.__("cat")."/{catName}/video/{videoName?}", function ($catName,$videoName = '') {
    $_GET['catName'] = $catName;
    $_GET['videoName'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);

SimpleRouter::get($basePath.__("cat")."/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."video/{videoName?}", function ($videoName = '') {
    $_GET['videoName'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
SimpleRouter::get($basePath."v/{videoName?}", function ($videoName = '') {
    $_GET['v'] = $videoName;
    require_once 'view/index.php'; exit;
},['defaultParameterRegex' => '[\w\-]+']);
// if it's used external, by encoder or so
SimpleRouter::post($basePath.__("login"), function() {
    require_once "objects/login.json.php"; exit;
});
SimpleRouter::get($basePath.__("login"), function() {
    require_once "objects/login.json.php"; exit;
});

?>
