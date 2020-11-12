<?php
global $isLive;
$isLive = 1;
$isEmbed = 1;
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';

if (!empty($_GET['c'])) {
    $user = User::getChannelOwner($_GET['c']);
    if (!empty($user)) {
        $_GET['u'] = $user['user'];
    }
}
$customizedAdvanced = AVideoPlugin::getObjectDataIfEnabled('CustomizeAdvanced');

$livet = LiveTransmition::getFromDbByUserName($_GET['u']);
$uuid = LiveTransmition::keyNameFix($livet['key']);
$p = AVideoPlugin::loadPlugin("Live");
$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}
$u = new User(0, $_GET['u'], false);
$user_id = $u->getBdId();
$video['users_id'] = $user_id;
AVideoPlugin::getModeYouTubeLive($user_id);
$_REQUEST['live_servers_id'] = Live::getLiveServersIdRequest();
$poster = Live::getPosterImage($livet['users_id'], $_REQUEST['live_servers_id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo $global['webSiteRootURL']; ?>view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
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
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
            var player;
        </script>
    </head>

    <body>
        <div class="">
            <video poster="<?php echo $global['webSiteRootURL']; ?><?php echo $poster; ?>?<?php echo filectime($global['systemRootPath'] . $poster); ?>" controls  playsinline webkit-playsinline="webkit-playsinline" 
                   class="video-js vjs-default-skin vjs-big-play-centered"
                   id="mainVideo" style="width: 100%; height: 100%; position: absolute;">
                <source src="<?php echo Live::getM3U8File($uuid); ?>" type='application/x-mpegURL'>
            </video>
            <?php
            if (AVideoPlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                $style = VideoLogoOverlay::getStyle();
                $url = VideoLogoOverlay::getLink();
                ?>
                <div style="<?php echo $style; ?>">
                    <a href="<?php echo $url; ?>" target="_blank"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" alt="Logo" class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                </div>
            <?php } ?>
        </div>

        <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);">
            <?php
            $streamName = $uuid;
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            include $global['systemRootPath'] . 'plugin/Live/view/onlineUsers.php';
            ?>
        </div>

        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.min.js" type="text/javascript"></script>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/script.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/js-cookie/js.cookie.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-toast/jquery.toast.min.js" type="text/javascript"></script>
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
        <script>
<?php
echo PlayerSkins::getStartPlayerJS();
?>
        </script>
        <?php
        require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
        ?>
        <!-- getFooterCode start -->
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>  
        <!-- getFooterCode end -->
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
