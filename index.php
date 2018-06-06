<?php
global $global, $config;
ini_set('error_log', $global['systemRootPath'].'videos/youphptube.log');
require_once 'objects/simple-php-router/vendor/autoload.php';
require_once 'videos/configuration.php';
require_once 'objects/configuration.php';
$basePath = parse_url ($global['webSiteRootURL'], PHP_URL_PATH);
use Pecee\SimpleRouter\SimpleRouter;
$config = new Configuration();
// echo $global['webSiteRootURL'];
SimpleRouter::get($basePath, function() {
    require_once "view/index.php";
});

SimpleRouter::get($basePath, function() {
    require_once "view/index.php";
});
SimpleRouter::get($basePath."info", function() {
    require_once "view/info.php";
});
SimpleRouter::get($basePath."signUp", function() {
    require_once "view/signUp.php";
});
SimpleRouter::post($basePath."addNewAd", function() {
    require_once "objects/video_adsAddNew.json.php";
});
SimpleRouter::post($basePath."createUser", function() {
    require_once "objects/userCreate.json.php";
});
SimpleRouter::post($basePath."ads", function() {
    require_once "view/managerAds.php";
});
SimpleRouter::get($basePath."about", function() {
    require_once "view/about.php";
});
SimpleRouter::get($basePath."contact", function() {
    require_once "view/contact.php";
});
SimpleRouter::get($basePath."user", function() {
    require_once "view/user.php";
});
SimpleRouter::get($basePath."users", function() {
    require_once "view/managerUsers.php";
});
SimpleRouter::post($basePath."updateUser", function() {
    require_once "objects/userUpdate.json.php";
});
SimpleRouter::post($basePath."savePhoto", function() {
    require_once "objects/userSavePhoto.php";
});
SimpleRouter::post($basePath."saveBackground", function() {
    require_once "objects/userSaveBackground.php";
});
SimpleRouter::post($basePath."users.json", function() {
    require_once "objects/users.json.php";
});
SimpleRouter::post($basePath."captcha", function() {
    require_once "objects/getCaptcha.php";
});
SimpleRouter::post($basePath."login", function() {
    require_once "objects/login.json.php";
});
SimpleRouter::get($basePath."logoff", function() {
    require_once "objects/logoff.php";
});
SimpleRouter::get($basePath."charts", function() {
    require_once "view/charts.php";
});
SimpleRouter::get($basePath."update", function() {
    require_once "update/update.php";
});
SimpleRouter::get($basePath."comments", function() {
    require_once "view/managerComments.php";
});
SimpleRouter::post($basePath."status", function() {
    require_once "objects/status.json.php";
});
SimpleRouter::get($basePath."plugins", function() {
    require_once "view/managerPlugins.php";
});
SimpleRouter::post($basePath."pluginsAvailable.json", function() {
    require_once "objects/pluginsAvailable.json.php";
});
SimpleRouter::post($basePath."plugins.json", function() {
    require_once "objects/plugins.json.php";
});

SimpleRouter::get($basePath."channels", function() {
    require_once "view/channels.php";
});
SimpleRouter::get($basePath."channel/{channelName?}", function ($channelName = '') {
    $_GET['channelName'] = $channelName;
    require_once 'view/channel.php';
});
SimpleRouter::get($basePath."cat/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php';
});
SimpleRouter::get($basePath."upload", function () {
    require_once 'view/mini-upload-form/index.php';
});
SimpleRouter::get($basePath."fileUpload", function () {
    require_once 'view/mini-upload-form/upload.php';
});
SimpleRouter::get($basePath."cat/{catName?}", function ($catName = '') {
    $_GET['catName'] = $catName;
    require_once 'view/index.php';
});
SimpleRouter::get($basePath."video/{videoName?}", function ($videoName = '') {
    $_GET['videoName'] = $videoName;
    require_once 'view/index.php';
});
SimpleRouter::get($basePath."v/{videoName?}", function ($videoName = '') {
    $_GET['v'] = $videoName;
    require_once 'view/index.php';
});
SimpleRouter::start();
?>