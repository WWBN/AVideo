<?php
global $global, $config;
global $isEmbed;
$isEmbed = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$objSecure = YouPHPTubePlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}

require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';
$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

$playListData = array();
foreach ($playList as $value) {

    $sources = getVideosURL($value['filename']);
    $images = Video::getImageFromFilename($value['filename'], $value['type']);


    $src = new stdClass();
    $src->src = $images->thumbsJpg;
    $thumbnail = array($src);

    $playListSources = array();
    foreach ($sources as $value2) {
        if ($value2['type'] !== 'video') {
            continue;
        }
        $playListSources[] = new playListSource($value2['url']);
    }
    $playListData[] = new PlayListElement($value['title'], $value['description'], $value['duration'], $playListSources, $thumbnail, $images->poster);
}
//var_dump($playListData);exit;
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>

        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <?php
        require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
        echo YouPHPTubePlugin::getHeadCode();
        ?>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.css" rel="stylesheet">

        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-3.3.1.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.js" type="text/javascript"></script>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden;
                <?php
                if (!empty($customizedAdvanced->embedBackgroundColor)) {
                    echo "background-color: $customizedAdvanced->embedBackgroundColor !important;";
                }
                ?>

            }
            .vjs-control-bar{
                z-index: 1;
            }
        </style>

        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/seetalert/sweetalert.min.js";
        $jsFiles[] = "view/js/bootpag/jquery.bootpag.min.js";
        $jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
        $jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/js-cookie/js.cookie.js";
        $jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.plugins.min.js";
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <video style="width: 100%; height: 100%;" playsinline
        <?php if ($config->getAutoplay() && false) { // disable it for now   ?>
                   autoplay="true"
                   muted="muted"
               <?php } ?>
               preload="auto"
               controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo">
        </video>
        <div style="position: absolute; right: 0; top: 0; width: 35%; height: 100%; overflow-y: scroll; margin-right: -16px; " id="playListHolder">
            <div class="vjs-playlist" style="" id="playList">
                <!--
                  The contents of this element will be filled based on the
                  currently loaded playlist
                -->
            </div>
        </div>

        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <?php
        echo YouPHPTubePlugin::getFooterCode();
        ?>

        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/videojs-playlist/videojs-playlist.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.js"></script>
        <script>
            if (typeof player === 'undefined') {
                player = videojs('mainVideo');
            }

            player.playlist(<?php echo json_encode($playListData); ?>);
            player.playlist.autoadvance(0);
            // Initialize the playlist-ui plugin with no option (i.e. the defaults).
            player.playlistUi();
            var timeout;
            $(document).ready(function () {
                timeout = setTimeout(function () {
                    $('#playList').fadeOut();
                }, 2000);
                $('#playListHolder').mouseenter(function () {
                    $('#playList').fadeIn();
                    clearTimeout(timeout);
                });
                $('#playListHolder').mouseleave(function () {
                    timeout = setTimeout(function () {
                        $('#playList').fadeOut();
                    }, 1000);

                });
                
                //Prevent HTML5 video from being downloaded (right-click saved)?
                $('#mainVideo').bind('contextmenu', function () {
                    return false;
                });
            });
        </script>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>