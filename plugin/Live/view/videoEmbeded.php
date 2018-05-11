<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}

$t = LiveTransmition::getFromDbByUserName($_GET['u']);
$uuid = $t['key'];
$p = YouPHPTubePlugin::loadPlugin("Live");
$objSecure = YouPHPTubePlugin::getObjectDataIfEnabled('SecureVideosDirectory');
if (!empty($objSecure->disableEmbedMode)) {
    die('Embed Mode disabled');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-3.3.1.min.js" type="text/javascript"></script>
        <style>
        </style>
    </head>

    <body>
        <div class="embed-responsive  embed-responsive-16by9">
            <video poster="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg" controls 
                   class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" 
                   id="mainVideo" data-setup='{ "aspectRatio": "16:9",  "techorder" : ["flash", "html5"] }'>
                <source src="<?php echo $p->getPlayerServer(); ?>/<?php echo $uuid; ?>/index.m3u8" type='application/x-mpegURL'>
            </video>
            <?php
            $liveCount = YouPHPTubePlugin::loadPluginIfEnabled('LiveCountdownEvent');
            $html = array();
            if ($liveCount) {
                $html = $liveCount->getNextLiveApplicationFromUser($user_id);
            }
            foreach ($html as $value) {
                echo $value['html'];
            };
            ?>
        </div>

        <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);">
            <?php
            $streamName = $uuid;
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            include $global['systemRootPath'] . 'plugin/Live/view/onlineUsers.php';
            ?>
        </div>

        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/videojs-contrib-hls.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <script>

            $(document).ready(function () {
                player = videojs('mainVideo');
                player.ready(function () {
                    var err = this.error();
                    if (err && err.code) {
                        $('.vjs-error-display').hide();
                        $('#mainVideo').find('.vjs-poster').css({'background-image': 'url(<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Offline.jpg)'});
<?php
if (!empty($html)) {
    echo "showCountDown();";
}
?>
                    }
<?php
if ($config->getAutoplay()) {
    echo "this.play();";
}
?>

                });
                player.persistvolume({
                    namespace: "YouPHPTube"
                });
            });
        </script>
        <?php
        require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
        echo YouPHPTubePlugin::getFooterCode();
        ?>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>