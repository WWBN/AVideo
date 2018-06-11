<?php
global $global, $config;
if (!file_exists('.htaccess')) { ?>
  <!DOCTYPE html>
  <html lang="en">
      <head>
          <title>Error Page</title>
          <link href="view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
          <style>
              body {
                  margin: 0;
                  padding: 0;
                  background: #e7ecf0;
                  font-family: Arial, Helvetica, sans-serif;
              }
              * {
                  margin: 0;
                  padding: 0;
              }
              p {
                  font-size: 12px;
                  color: #373737;
                  font-family: Arial, Helvetica, sans-serif;
                  line-height: 18px;
              }
              p a {
                  color: #218bdc;
                  font-size: 12px;
                  text-decoration: none;
              }
              a {
                  outline: none;
              }
              .f-left {
                  float: left;
              }
              .f-right {
                  float: right;
              }
              .clear {
                  clear: both;
                  overflow: hidden;
              }
              #block_error {
                  width: 1000px;
                  height: 700px;
                  border: 1px solid #cccccc;
                  margin: 72px auto 0;
                  -moz-border-radius: 4px;
                  -webkit-border-radius: 4px;
                  border-radius: 4px;
                  background: #fff url(http://www.ebpaidrev.com/systemerror/block.gif) no-repeat 0 51px;
              }
              #block_error div {
                  padding: 10px 40px 0 186px;
              }
              #block_error div h2 {
                  color: #218bdc;
                  font-size: 24px;
                  display: block;
                  padding: 0 0 14px 0;
                  border-bottom: 1px solid #cccccc;
                  margin-bottom: 12px;
                  font-weight: normal;
              }
              img {
                  max-height: 50px;
                  margin: 10px 0 0 5px;
              }
          </style>
      </head>
      <body marginwidth="0" marginheight="0">

          <div id="block_error">
              <img src="view/img/logo.png" class="img img-responsive center-block"/>
              <div>
                  <h2>Error. Oops you've encountered an error</h2>
                  <p>
                      It appears that either something went wrong or the mod rewrite configration is not correct.<br />
                  </p>
                  <p><b>If you don't use apache, just let .htaccess stay or create a empty file - then this check will pass.</b></p>
                  <p>We need to allow Apache to read .htaccess files located under the <?php echo getcwd(); ?> directory.

                  You can do this by editing the Apache configuration file:</p>

                  <p>
                      Find the section <code><?php echo htmlentities("<directory /var/www/html>"); ?></code> and change <b>AllowOverride None</b> to <b>AllowOverride All</b>
                  </p>
                  <p><pre><code>sudo nano /etc/apache2/apache2.conf</code></pre></p>

                  <p>
                      After editing the above file your code should be like this:
                  </p>
                  <p><pre><code><?php echo htmlentities("<Directory /var/www/>
          Options Indexes FollowSymLinks
          AllowOverride All
          Require all granted
  </Directory>"); ?></code></pre></p>

                  <p>In order to use mod_rewrite you can type the following command in the terminal:</p>
                  <p><pre><code>sudo a2enmod rewrite</code></pre></p>

                  <p>Restart apache2 after</p>

                  <p><pre><code>sudo /etc/init.d/apache2 restart</code></pre></p>

                  <p>or</p>

                  <p><pre><code>sudo service apache2 restart</code></pre></p>

              </div>
          </div>
      </body>
  </html>
<?php exit; } // if .htaccess not exist
ini_set('error_log', $global['systemRootPath'].'videos/youphptube.log');
// it is needed for installation
if (!file_exists('videos/configuration.php')) {
    if (!file_exists('install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once 'objects/simple-php-router/vendor/autoload.php';
require_once 'videos/configuration.php';
require_once 'objects/configuration.php';
$basePath = parse_url ($global['webSiteRootURL'], PHP_URL_PATH);
use Pecee\SimpleRouter\SimpleRouter;
$config = new Configuration();
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
SimpleRouter::get($basePath."ads", function() {
    require_once "view/managerAds.php"; exit;
});
SimpleRouter::post($basePath."ads.json", function() {
    require_once "objects/video_ads.json.php"; exit;
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
/*
SimpleRouter::post($basePath."youtubeUpload", function() {
    require_once "objects/youtubeUpload.json.php";
});
SimpleRouter::post($basePath."addNewAd", function() {
    require_once "objects/video_adsAddNew.json.php";
});
SimpleRouter::post($basePath."setStatusVideo", function() {
    require_once "objects/videoStatus.json.php";
});
SimpleRouter::post($basePath."setCategoryVideo", function() {
    require_once "objects/videoCategory.json.php";
});
SimpleRouter::post($basePath."refreshVideo", function() {
    require_once "objects/videoRefresh.json.php";
});
SimpleRouter::post($basePath."pluginsAvailable.json", function() {
    require_once "objects/pluginsAvailable.json.php";
});
SimpleRouter::post($basePath."rotateVideo", function() {
    require_once "objects/videoRotate.json.php";
});
SimpleRouter::post($basePath."reencodeVideo", function() {
    require_once "objects/videoReencode.json.php";
});
SimpleRouter::post($basePath."saveComment", function() {
    require_once "objects/commentAddNew.json.php";
});
SimpleRouter::post($basePath."fileUpload", function () {
    require_once 'view/mini-upload-form/upload.php';
});
SimpleRouter::post($basePath."addNewVideo", function() {
    require_once "objects/videoAddNew.json.php";
});
SimpleRouter::post($basePath."createUser", function() {
    require_once "objects/userCreate.json.php";
});

SimpleRouter::get($basePath."subscribe.json", function() {
    require_once "objects/subscribe.json.php";
});
SimpleRouter::post($basePath."deleteVideo", function() {
    require_once "objects/videoDelete.json.php";
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
SimpleRouter::get($basePath."videos.json", function() {
    require_once "objects/videos.json.php";
});
SimpleRouter::post($basePath."addViewCountVideo", function() {
    require_once "objects/videoAddViewCount.json.php";
});
*/
SimpleRouter::start();
?>
