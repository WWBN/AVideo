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

$objectToReturnToParentIframe = new stdClass();
$objectToReturnToParentIframe->videos_id = 0;
$objectToReturnToParentIframe->isLive = false;
$objectToReturnToParentIframe->isVOD = false;
$objectToReturnToParentIframe->title = '';
$objectToReturnToParentIframe->posterURL = '';
$objectToReturnToParentIframe->duration = '';
$objectToReturnToParentIframe->views_count = 0;
$objectToReturnToParentIframe->videoHumanTime = '';
$objectToReturnToParentIframe->creator = '';
$objectToReturnToParentIframe->live_transmitions_id = 0;
$objectToReturnToParentIframe->users_id = 0;
$objectToReturnToParentIframe->key = '';

$liveVideo = Live::getLatest(true);
//var_dump($liveVideo);exit;
if (!empty($liveVideo)) {
    setLiveKey($liveVideo['key'], $liveVideo['live_servers_id'], $liveVideo['live_index']);
    $poster = getURL(Live::getPosterImage($liveVideo['users_id'], $liveVideo['live_servers_id']));
    $sources = "<source src=\"" . Live::getM3U8File($liveVideo['key']) . "\" type=\"application/x-mpegURL\">";
    $objectToReturnToParentIframe->isLive = true;
    $objectToReturnToParentIframe->title = Live::getTitleFromKey($liveVideo['key']);

    $objectToReturnToParentIframe->duration = __('Live');
    $objectToReturnToParentIframe->videoHumanTime = __('Now');
    $objectToReturnToParentIframe->creator = User::getNameIdentificationById($liveVideo['users_id']);
    
    $objectToReturnToParentIframe->mediaSession = Live::getMediaSession($liveVideo['key'], $liveVideo['live_servers_id']);
    $objectToReturnToParentIframe->live_transmitions_id = intval($liveVideo['live_transmitions_id']);
    $objectToReturnToParentIframe->live_transmitions_history_id = intval($liveVideo['live_transmitions_history_id']);
    $objectToReturnToParentIframe->users_id = intval($liveVideo['users_id']);
    $objectToReturnToParentIframe->key = $liveVideo['key'];
} else {
    $_POST['rowCount'] = 1;
    $_POST['sort']['created'] = 'DESC';
    $videos = Video::getAllVideos();
    if (empty($videos)) {
        videoNotFound('');
    }
    $video = $videos[0];
    $poster = Video::getPoster($video['id']);
    $sources = getSources($video['filename']);
    $objectToReturnToParentIframe->videos_id = intval($video['id']);
    $objectToReturnToParentIframe->isVOD = true;
    $objectToReturnToParentIframe->title = $video['title'];
    $objectToReturnToParentIframe->duration = $video['duration'];
    $objectToReturnToParentIframe->views_count = $video['views_count'];
    $objectToReturnToParentIframe->videoHumanTime = humanTiming(strtotime($video['videoCreation']), 0, true, true);
    $objectToReturnToParentIframe->creator = User::getNameIdentificationById($video['users_id']);
    
    $objectToReturnToParentIframe->mediaSession = Video::getMediaSession($video['id']);
    $objectToReturnToParentIframe->users_id = intval($liveVideo['users_id']);
}
$objectToReturnToParentIframe->user = User::getNameIdentificationById($objectToReturnToParentIframe->users_id);
$objectToReturnToParentIframe->UserPhoto = User::getPhoto($objectToReturnToParentIframe->users_id);
$objectToReturnToParentIframe->posterURL = $poster;

//var_dump($liveVideo, $video['id'], $poster, $sources);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="<?php echo getURL('view/img/favicon.ico'); ?>">
        <title><?php echo $objectToReturnToParentIframe->title; ?></title>
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
                overflow:hidden;
            }
                <?php
                if (!empty($customizedAdvanced->embedBackgroundColor)) {
                    echo "body {background-color: $customizedAdvanced->embedBackgroundColor;}";
                }
                ?>
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
        <script>
            /*
             * add this code in your parent page
             window.addEventListener("message", function (event) {
             console.log(event.data);
             });
             */
            parent.postMessage(<?php echo _json_encode($objectToReturnToParentIframe); ?>, "*");
            function pausePlayer(){
                player.pause();
            }
            window.addEventListener('message', event => {
                switch (event.data.type) {
                    case 'pausePlayer':
                        player.pause();
                        break;
                
                    default:
                        break;
                }
            });
        </script>
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
