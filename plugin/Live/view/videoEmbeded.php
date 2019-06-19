<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$customizedAdvanced = YouPHPTubePlugin::getObjectDataIfEnabled('CustomizeAdvanced');

$t = LiveTransmition::getFromDbByUserName($_GET['u']);
$uuid = $t['key'];
$p = YouPHPTubePlugin::loadPlugin("Live");
$objSecure = YouPHPTubePlugin::loadPluginIfEnabled('SecureVideosDirectory');
if(!empty($objSecure)){
    $objSecure->verifyEmbedSecurity();
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/videojs-contrib-ads/videojs.ads.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.3.1.min.js" type="text/javascript"></script>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                <?php
                if (!empty($customizedAdvanced->embedBackgroundColor)) {
                    echo "background-color: $customizedAdvanced->embedBackgroundColor;";
                }
                ?>
                overflow:hidden;
            }
        </style>
    </head>

    <body>
        <div class="">
            <video poster="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg" controls
                   class="video-js vjs-default-skin vjs-big-play-centered"
                   id="mainVideo" data-setup='{  "techorder" : ["flash", "html5"] }' style="width: 100%; height: 100%; position: absolute;">
                <source src="<?php echo $p->getM3U8File($uuid); ?>" type='application/x-mpegURL'>
            </video>
            <?php
            if (YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>">
                    <a href="<?php echo $url; ?>" target="_blank"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                </div>
            <?php } ?>

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

        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/videojs-contrib-ads/videojs.ads.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/videojs-contrib-hls.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <?php
        echo YouPHPTubePlugin::getHeadCode();
        ?>
        <script>

            $(document).ready(function () {
                if (typeof player === 'undefined') {
                    player = videojs('mainVideo');
                }
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
        <?php
        if (empty($liveDO->disableDVR)) {
            ?>
            <script src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/videojs-dvr/videojs-dvrseekbar.min.js" type="text/javascript"></script>          
            <script>
                $(document).ready(function () {
                    if (typeof player === 'undefined') {
                        player = videojs('mainVideo');
                    }

                    player.dvrseekbar();
                });
            </script>      
            <?php
        }
        ?>    
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
