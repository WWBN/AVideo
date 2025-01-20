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
$global['ignorePersistVolume'] = 1;
$global['ignoreMessageOfTheDay'] = 1;
$global['ignoreADOverlay'] = 1;
$_GET['noChat'] = 1;
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

$users_id = 0;
if (!empty($_REQUEST['channelName'])) {
    $user = User::getChannelOwner($_REQUEST['channelName']);
    $users_id = $user['id'];
}

$categories_id = 0;
if (!empty($_REQUEST['catName'])) {
    $cat = Category::getCategoryByName($_REQUEST['catName']);
    $categories_id = $cat['id'];
}

function matchWithRequest($row)
{
    global $users_id, $categories_id;
    if (!empty($row['users_id']) && !empty($users_id)) {
        return $row['users_id'] == $users_id;
    }
    if (!empty($row['categories_id']) && !empty($categories_id)) {
        return $row['categories_id'] == $categories_id;
    }
    return true;
}

$liveFound = false;
$isEnabledPayPerViewLive = AVideoPlugin::isEnabledByName("PayPerViewLive");
if (AVideoPlugin::isEnabledByName('PlayLists')) {
    // try to get a live that is not a scheduled playlist
    $lives = LiveTransmitionHistory::getActiveLives('', false);
    foreach ($lives as $key => $value) {
        if ($isEnabledPayPerViewLive && !PayPerViewLive::canUserWatchNow(User::getId(), $value['users_id'])) {
            continue;
        }
        if (!Playlists_schedules::iskeyPlayListScheduled($value['key'])) {
            if (matchWithRequest($value)) {
                $liveVideo = $value;
                $liveFound = true;
                break;
            }
        }
    }
}
if (!$liveFound) {
    //$liveVideo = Live::getLatest(true, $users_id, $categories_id);

    $activeLives = LiveTransmitionHistory::getActiveLives('', true, $users_id);
    foreach ($activeLives as $key => $value) {
        if ($isEnabledPayPerViewLive && !PayPerViewLive::canUserWatchNow(User::getId(), $value['users_id'])) {
            continue;
        }
        $liveVideo = $value;
        break;
    }
}
//var_dump($liveFound, $liveVideo);exit;
if (!empty($liveVideo)) {
    setLiveKey($liveVideo['key'], $liveVideo['live_servers_id'], $liveVideo['live_index']);
    $poster = getURL(Live::getRegularPosterImage($liveVideo['users_id'], $liveVideo['live_servers_id'], 0, 0));
    $m3u8 = Live::getM3U8File($liveVideo['key']);
    if (!empty($m3u8)) {
        $sources = "<source src=\"{$m3u8}\" type=\"application/x-mpegURL\">";
        $objectToReturnToParentIframe->isLive = true;
        $objectToReturnToParentIframe->title = Live::getTitleFromKey($liveVideo['key']);

        $objectToReturnToParentIframe->duration = __('Live');
        $objectToReturnToParentIframe->videoHumanTime = __('Now');
        $objectToReturnToParentIframe->creator = User::getNameIdentificationById($liveVideo['users_id']);

        $objectToReturnToParentIframe->mediaSession = Live::getMediaSession($liveVideo['key'], $liveVideo['live_servers_id'], 0, 0);
        $objectToReturnToParentIframe->live_transmitions_id = intval($liveVideo['live_transmitions_id']);
        $objectToReturnToParentIframe->live_transmitions_history_id = intval($liveVideo['live_transmitions_history_id']);
        $objectToReturnToParentIframe->users_id = intval($liveVideo['users_id']);
        $objectToReturnToParentIframe->key = $liveVideo['key'];

        $liveFound = true;
    }
}

if (!$liveFound && AVideoPlugin::isEnabledByName('LiveLinks')) {
    $_POST['rowCount'] = 1;
    $_POST['sort']['created'] = 'DESC';
    $liveVideo = LiveLinks::getAllActive(false, true, false, $users_id, $categories_id);
    $video = $liveVideo[0];
    if (!empty($video['link']) && isValidURL($video['link'])) {
        $poster = LiveLinks::getImage($video['id']);
        $sources = "<source src=\"{$video['link']}\" type=\"application/x-mpegURL\">";
        $objectToReturnToParentIframe->isLive = true;
        $objectToReturnToParentIframe->title = $video['title'];

        $objectToReturnToParentIframe->duration = __('Live');
        $objectToReturnToParentIframe->videoHumanTime = __('Now');
        $objectToReturnToParentIframe->creator = User::getNameIdentificationById($video['users_id']);

        $objectToReturnToParentIframe->mediaSession = LiveLinks::getMediaSession($video['id']);
        $objectToReturnToParentIframe->users_id = intval($video['users_id']);
        $liveFound = true;
        $isLiveLink = uniqid();
    }
}
if (!$liveFound) {
    $_POST['rowCount'] = 1;

    //try suggested only first
    $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, $users_id, false, [], false, false, true, true, null, Video::$videoTypeVideo, 0);

    if (empty($videos)) {
        $_POST['sort']['created'] = 'DESC';
        //getAllVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = [], $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false, $is_serie = null, $type = '', $max_duration_in_seconds = 0)
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, $users_id, false, [], false, false, true, false, null, Video::$videoTypeVideo, 0);
        if (empty($videos)) {
            videoNotFound('');
        }
    }
    $video = $videos[0];
    $_GET['videos_id'] = $video['id'];
    $_REQUEST['videos_id'] = $video['id'];
    $poster = Video::getPoster($video['id']);
    if ($video['type'] == Video::$videoTypeLinkVideo) {
        $sources = getSourceFromURL($video['videoLink']);
    } else {
        $sources = getSources($video['filename']);
    }
    //var_dump($sources, $video['type'], Video::$videoTypeLinkVideo, $video['videoLink']);exit;
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

$bodyClass = '';
if (!empty($_REQUEST['isClosed'])) {
    $bodyClass = 'is-closed';
}
//var_dump($liveVideo, $video['id'], $poster, $sources);exit;
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">

<head>
    <!-- version 2024-10-01 -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo $config->getFavicon(true); ?>">
    <title><?php echo $objectToReturnToParentIframe->title; ?></title>
    <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo getURL('view/css/main.css'); ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo getURL('view/js/session.js'); ?>" type="text/javascript"></script>
    <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
    <style>
        body {
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
            background-color: #000;
        }

        .videoViews {
            margin-top: 25px;
        }

        .liveEmbed .liveOnlineLabel.label-danger,
        body.is-closed #mainVideo>button.vjs-big-play-button,
        body.is-closed #mainVideo .videoTagsLabelsElement {
            display: none !important;
        }
    </style>
    <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css" />
    
    <?php
    echo AVideoPlugin::afterVideoJS();
    ?>
    <script>
        <?php
        if (!empty($objectToReturnToParentIframe->videos_id)) {
            echo "var mediaId = {$objectToReturnToParentIframe->videos_id};";
            echo "var videos_id = {$objectToReturnToParentIframe->videos_id};";
        }
        ?>
        var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        var player;
    </script>
    <?php
    echo AVideoPlugin::getHeadCode();
    ?>
</head>

<body class="<?php echo $bodyClass; ?>">
    <div id="videoDiv" style="display: none;">
        <video poster="<?php echo $poster; ?>" controls <?php echo PlayerSkins::getPlaysinline(); ?> class="video-js vjs-default-skin vjs-big-play-centered" id="mainVideo" style="width: 100%; height: 100%; position: absolute;">
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
    <script src="<?php echo getURL('view/js/addView.js'); ?>" type="text/javascript"></script>
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
        doNotCountView = <?php echo !empty($_REQUEST['isClosed']) ? 'true' : 'false'; ?>;

        /*
             * add this code in your parent page
             window.addEventListener("message", function (event) {
             console.log(event.data);
             });
             */
        parent.postMessage(<?php echo _json_encode($objectToReturnToParentIframe); ?>, "*");

        function pausePlayer() {
            player.pause();
        }
        window.addEventListener('message', event => {
            switch (event.data.type) {
                case 'pausePlayer':
                    player.pause();
                    break;
                case 'playerMute':
                    player.muted(true);
                    break;
                case 'playerUnmute':
                    player.muted(false);
                    addCurrentView();
                    break;
                case 'userInactive':
                    $('#mainVideo').removeClass('vjs-user-active');
                    $('#mainVideo').addClass('vjs-user-inactive');
                    break;
                case 'open':
                    $('body').addClass('is-opened');
                    $('body').removeClass('is-closed');
                    break;
                case 'close':
                    $('body').addClass('is-closed');
                    $('body').removeClass('is-opened');
                    break;
                default:
                    break;
            }
        });
        $(document).ready(function() {
            <?php
            echo PlayerSkins::getStartPlayerJS();
            if (!empty($_REQUEST['muted'])) {
                echo 'player.muted(true);';
            }
            ?>
            setTimeout(function() {
                $('#videoDiv').fadeIn();
                player.on('volumechange', function() {
                    if (player.muted()) {
                        doNotCountView = true;
                        console.log('_addView doNotCountView 1', doNotCountView);
                    } else {
                        doNotCountView = false;
                        console.log('_addView doNotCountView 2', doNotCountView);
                    }
                });
            }, 1000);
            playerPlay(0);
        });
    </script>
</body>

</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>