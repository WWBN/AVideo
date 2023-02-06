<?php
global $isLive;
$isLive = 1;
$isEmbed = 1;
require_once '../../videos/configuration.php';
$p = AVideoPlugin::loadPlugin("Live");
$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}

$liveVideo = Live::getLatest(true);
if (!empty($liveVideo)) {
    setLiveKey($liveVideo['key'], $liveVideo['live_servers_id'], $liveVideo['live_index']);
    $poster = getURL(Live::getPosterImage($liveVideo['users_id'], $liveVideo['live_servers_id']));
    $sources = "<source src=\"" . Live::getM3U8File($liveVideo['key']) . "\" type=\"application/x-mpegURL\">";
} else {
    $_POST['rowCount'] = 1;
    $videos = Video::getAllVideos();
    if (empty($videos)) {
        videoNotFound('');
    }
    $video = $videos[0];
    $poster = Video::getPoster($video['id']);
    $sources = getSources($video['filename']);
}
//var_dump($liveVideo, $video['id'], $poster, $sources);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo getCDN(); ?>view/img/favicon.ico">
        <title><?php echo $liveTitle; ?></title>
        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('node_modules/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"/>
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
        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
    </head>

    <body>
        <div class="">
            <video poster="<?php echo $poster; ?>" controls  <?php echo PlayerSkins::getPlaysinline(); ?> 
                   class="video-js vjs-default-skin vjs-big-play-centered"
                   id="mainVideo" style="width: 100%; height: 100%; position: absolute;">
                       <?php echo $sources; ?>
            </video>
        </div>

        <div style="z-index: 999; position: absolute; top:5px; left: 5px; opacity: 0.8; filter: alpha(opacity=80);" class="liveEmbed">
            <?php
            $streamName = $uuid;
            include $global['systemRootPath'] . 'plugin/Live/view/onlineLabel.php';
            echo getLiveUsersLabel();
            ?>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>
        <script src="<?php echo getURL('view/js/script.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/js-cookie/dist/js.cookie.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/jquery-toast-plugin/dist/jquery.toast.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/sweetalert/dist/sweetalert.min.js'); ?>" type="text/javascript"></script>
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
        showCloseButton();
        ?>  
        <!-- getFooterCode end -->
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
