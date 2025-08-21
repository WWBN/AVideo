<?php
global $global, $config;
global $isEmbed;
$isEmbed = 1;
$isPlayList = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$timelogname = __FILE__;
TimeLogStart($timelogname);
$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}

TimeLogEnd($timelogname, __LINE__);
require_once $global['systemRootPath'] . 'objects/playlist.php';
TimeLogEnd($timelogname, __LINE__);
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';
TimeLogEnd($timelogname, __LINE__);

if (!User::isAdmin() && !PlayList::canSee($_GET['playlists_id'], User::getId())) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$playlist_index = intval(@$_REQUEST['playlist_index']);

TimeLogEnd($timelogname, __LINE__);
$pl = new PlayList($_GET['playlists_id']);

TimeLogEnd($timelogname, __LINE__);
$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

TimeLogEnd($timelogname, __LINE__);
$playListData = array();
$playListData_videos_id = array();
$collectionsList = PlayList::showPlayListSelector($playList);
TimeLogEnd($timelogname, __LINE__);
$videoStartSeconds = array();

$users_id = User::getId();

setPlayListIndex(0);

TimeLogEnd($timelogname, __LINE__);
$TimeLogLimit = 0.2;
foreach ($playList as $key => $value) {
    $oldValue = $value;
    $timelognameF = __FILE__.'::foreach';
    TimeLogStart($timelognameF);

    if (!User::isAdmin() && !Video::userGroupAndVideoGroupMatch($users_id, $value['videos_id'])) {
        unset($playList[$key]);
        continue;
    }

    if ($key == $playlist_index) {
        setPlayListIndex(count($playListData));
    }

    TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
    if ($oldValue['type'] === Video::$videoTypeSerie && !empty($oldValue['serie_playlists_id'])) {
        $subPlayList = PlayList::getVideosFromPlaylist($value['serie_playlists_id']);
        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        foreach ($subPlayList as $value) {
            $timelognameFF = __FILE__.'::foreach::foreach';
            TimeLogStart($timelognameFF);
            $sources = getVideosURL($value['filename']);
            $images = Video::getImageFromFilename($value['filename'], $value['type']);
            $externalOptions = _json_decode($value['externalOptions']);

            TimeLogEnd($timelognameFF, __LINE__, $TimeLogLimit);
            $src = new stdClass();
            $src->src = $images->thumbsJpg;
            $thumbnail = array();
            $thumbnail[] = $src;

            $playListSources = array();
            foreach ($sources as $value2) {
                if ($value2['type'] !== Video::$videoTypeVideo && $value2['type'] !== Video::$videoTypeAudio) {
                    continue;
                }
                $playListSources[] = new playListSource($value2['url'], $value2['videos_id']);
                break;
            }
            TimeLogEnd($timelognameFF, __LINE__, $TimeLogLimit);
            if (empty($playListSources)) {
                continue;
            }
            if (User::isLogged()) {
                $videoStartSeconds = Video::getLastVideoTimePosition($value['videos_id']);
            }
            TimeLogEnd($timelognameFF, __LINE__, $TimeLogLimit);

            if (empty($videoStartSeconds)) {
                $videoStartSeconds = parseDurationToSeconds(@$externalOptions->videoStartSeconds);
            }
            TimeLogEnd($timelognameFF, __LINE__, $TimeLogLimit);

            $playListData[] = new PlayListElement(@$value['title'], @$value['description'], @$value['duration'], $playListSources, $thumbnail, $images->poster, $videoStartSeconds, $value['cre'], @$value['likes'], @$value['views_count'], @$value['videos_id'], "embedPlayList subPlaylistCollection-{$oldValue['serie_playlists_id']}");
            //$playListData_videos_id[] = $value['id'];
        }
        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
    } else {
        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        $sources = getVideosURL($value['filename']);
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $externalOptions = _json_decode($value['externalOptions']);

        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        $src = new stdClass();
        $src->src = $images->thumbsJpg;
        $thumbnail = array();
        $thumbnail[] = $src;

        $playListSources = array();
        foreach ($sources as $value2) {
            if ($value2['type'] !== 'video' && $value2['type'] !== 'audio') {
                continue;
            }
            $playListSources[] = new playListSource($value2['url'], $value2['videos_id']);
            break;
        }

        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        if (function_exists('getVTTTracks')) {
            $subtitleTracks = getVTTTracks($value['filename'], true);
        }

        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        if (empty($playListSources)) {
            continue;
        }

        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        if (User::isLogged()) {
            $videoStartSeconds = Video::getLastVideoTimePosition($value['videos_id']);
        }

        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        if (empty($videoStartSeconds)) {
            $videoStartSeconds = parseDurationToSeconds(@$externalOptions->videoStartSeconds);
        }
        TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
        $playListData[] = new PlayListElement(@$value['title'], @$value['description'], @$value['duration'], $playListSources, $thumbnail, $images->poster, $videoStartSeconds, $value['cre'],@$value['likes'], @$value['views_count'], @$value['videos_id'], "embedPlayList ", $subtitleTracks);
        //$playListData_videos_id[] = $value['videos_id'];
    }
    TimeLogEnd($timelognameF, __LINE__, $TimeLogLimit);
}

TimeLogEnd($timelogname, __LINE__);
$playListData_videos_id = getPlayListDataVideosId();

TimeLogEnd($timelogname, __LINE__);
if (empty($playListData)) {
    $plp = new PlayListPlayer(@$_GET['playlists_id'], @$_GET['playlists_tags_id']);
    $video = $plp->getCurrentVideo();
    if (!empty($video)) {
        $url = Video::getURL($video['id']);;
        if (!empty($url)) {
            header('Location: ' . $url);
            exit;
        }
    }
    _error_log("The program is empty ".json_encode($messagesFromPlayList));
    forbiddenPage(__("The program is empty"));
}

$url = PlayLists::getLink($pl->getId());
TimeLogEnd($timelogname, __LINE__);
$title = $pl->getName();

TimeLogEnd($timelogname, __LINE__);
if ($serie = PlayLists::isPlayListASerie($pl->getId())) {
    setVideos_id($serie['id']);
} else if (!empty($playListData_videos_id[getPlayListIndex()])) {
    setVideos_id($playListData_videos_id[getPlayListIndex()]);
}
$_REQUEST['hideAutoplaySwitch'] = 1;
//var_dump($playListData_videos_id);exit;
$pl_index = getPlayListIndex();
$str = file_get_contents($global['systemRootPath'] . 'plugin/PlayLists/getStartPlayerJS.js');
$str = str_replace('{$pl_index}', $pl_index, $str);
TimeLogEnd($timelogname, __LINE__);
PlayerSkins::getStartPlayerJS($str);
TimeLogEnd($timelogname, __LINE__);
?>

<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>

        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getURL('view/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet" type="text/css"/>

        <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('view/css/social.css'); ?>" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getURL('node_modules/@fortawesome/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css"/>

        <link href="<?php echo getCDN(); ?>node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.css" rel="stylesheet">

        <script src="<?php echo getURL('node_modules/jquery/dist/jquery.min.js'); ?>" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>

        <?php
        //echo AVideoPlugin::getHeadCode();
        ?>
        <style>
            body {
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden;
            }
            .vjs-control-bar{
                z-index: 1;
            }
            .form-control{
                background-color: #333 !important;
                color: #AAA !important;
            }

            #playListHolder{
                position: absolute;
                right: 0;
                top: 0;
                width: 30%;
                max-width: 320px;
                height: 100%;
                overflow-y: scroll;
                margin-right: 0;
                background-color: #00000077;
            }
            #playList{
                margin-top: 40px;
                margin-bottom: 60px;
            }
            #playListFilters{
                width: 30%;
                max-width: 320px;
                display: inline-flex;
                position: fixed;
                top: 0;
                right: 0;
                z-index: 1;
            }
            .vjs-playlist .vjs-playlist-duration{
                display: unset !important;
            }
        </style>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <video style="width: 100%; height: 100%;" <?php echo PlayerSkins::getPlaysinline(); ?>
        <?php if ($config->getAutoplay() && false) { // disable it for now     ?>
                   autoplay="true"
                   muted="muted"
               <?php } ?>
               preload="auto"
               controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo">
        </video>
        <?php
        include $global['systemRootPath'] . 'plugin/PlayLists/playListHolder.html.php';
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>
        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/BootstrapMenu.min.js";
        $jsFiles[] = "node_modules/sweetalert/dist/sweetalert.min.js";
        //$jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
        //$jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/addView.js";
        $jsFiles[] = "node_modules/js-cookie/dist/js.cookie.js";
        //$jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
        $jsFiles[] = "node_modules/jquery-lazy/jquery.lazy.min.js";
        $jsFiles[] = "node_modules/jquery-lazy/jquery.lazy.plugins.min.js";
        $jsFiles[] = "node_modules/jquery-toast-plugin/dist/jquery.toast.min.js";
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/bootstrap.js.php';
        ?>
        <?php
        echo combineFilesHTML($jsFiles, 'js', true);
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <script src="<?php echo getURL('node_modules/jquery-ui-dist/jquery-ui.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/videojs-playlist/dist/videojs-playlist.min.js'); ?>" type="text/javascript"></script>
        <script src="<?php echo getURL('node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.min.js'); ?>" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/moment.js.php';
        ?>
        <script>
            var embed_playerPlaylist = <?php echo json_encode($playListData); ?>;
            var originalPlayerPlaylist = embed_playerPlaylist;
            var updatePLSourcesTimeout;
            var isPlayListPlaying = 0;

            function setCurrentPlaylitItemVideoStartSeconds(videoStartSeconds) {
                if (typeof embed_playerPlaylist[player.playlist.currentIndex()] !== 'undefined') {
                    embed_playerPlaylist[player.playlist.currentIndex()].videoStartSeconds = videoStartSeconds;
                }
            }

            function addViewOnCurrentPlaylitItem(currentTime) {
                var videos_id = getCurrentPlaylitItemVideosId();
                if (videos_id) {
                    addView(videos_id, currentTime);
                }
            }

            function getCurrentPlaylitItemVideosId() {
                if (typeof embed_playerPlaylist[player.playlist.currentIndex()] !== 'undefined' && !empty(embed_playerPlaylist[player.playlist.currentIndex()].videos_id)) {
                    return embed_playerPlaylist[player.playlist.currentIndex()].videos_id;
                }
                return 0;
            }

            function updatePLSources(_index) {
                if (_index < 0) {
                    _index = 0;
                }
                clearTimeout(updatePLSourcesTimeout);
                if (typeof player.updateSrc == 'function' && typeof player.videoJsResolutionSwitcher != 'undefined' && typeof embed_playerPlaylist[_index] != 'undefined' && typeof embed_playerPlaylist[_index].sources != 'undefined') {
                    console.log('updatePLSources', _index);
                    //player.src(embed_playerPlaylist[_index].sources);
                    player.updateSrc(embed_playerPlaylist[_index].sources);
                    console.log('player.currentTime', embed_playerPlaylist[_index].videoStartSeconds);
                    currentTime = embed_playerPlaylist[_index].videoStartSeconds;
                    startCurrentTime = currentTime;
                    forceCurrentTime = currentTime;
                    player.currentTime(embed_playerPlaylist[_index].videoStartSeconds);
                    //player.currentResolution(embed_playerPlaylist[_index].sources[0].label);
                    //player.currentResolution(embed_playerPlaylist[_index].sources[0].label);
                    //player.updateSrc(embed_playerPlaylist[_index].sources);
                    userIsControling = false;
                    reloadAds();

                    if (typeof embed_playerPlaylist[_index] !== 'undefined') {
                        updatePLSourcesTimeout = setTimeout(function () {
                            if (!isPlayListPlaying) {
                                playerPlayIfAutoPlay(embed_playerPlaylist[_index].videoStartSeconds);
                            } else {
                                playerPlay(embed_playerPlaylist[_index].videoStartSeconds);
                            }
                            isPlayListPlaying = 1;
                            if (embed_playerPlaylist[_index].tracks && embed_playerPlaylist[_index].tracks.length) {
                                var _tracks = embed_playerPlaylist[_index].tracks;
                                setTimeout(function () {
                                    for (let j = 0; j < _tracks.length; j++) {
                                        console.log('tracks ', _tracks[j]);
                                        player.addRemoteTextTrack({kind: 'captions', label: _tracks[j].label, src: _tracks[j].src}, false);
                                    }
                                }, 1000);
                            }
                            console.log('player.playlist.currentItem', _index);
                            player.playlist.currentItem(_index);
                        }, 1000);
                    }
                } else {
                    updatePLSourcesTimeout = setTimeout(function () {
                        updatePLSources(_index);
                    }, 500);
                    return false;
                }
            }
        </script>
        <?php
        echo AVideoPlugin::afterVideoJS();
        ?>
        <script>
            $(document).ready(function () {

                $("#playListSearch").keyup(function () {
                    var filter = $(this).val();
                    $(".vjs-playlist-item-list li").each(function () {
                        if ($(this).find('.vjs-playlist-name').text().search(new RegExp(filter, "i")) < 0) {
                            $(this).slideUp();
                        } else {
                            $(this).slideDown();
                        }
                    });
                });

                $('#embededSortBy').change(function () {
                    var value = $(this).val();
                    embed_playerPlaylist.sort(function (a, b) {
                        return compare(a, b, value);
                    });
                    player.playlist.sort(function (a, b) {
                        return compare(a, b, value);
                    });
                });

                $('#subPlaylistsCollection').change(function () {
                    var value = parseInt($(this).val());
                    console.log('subPlaylistsCollection', value);
                    if (value) {
                        var className = '.subPlaylistCollection-' + value;
                        $(className).slideDown();
                        $('.embedPlayList').not(className).slideUp();
                    } else {
                        $('.embedPlayList').slideDown();
                    }
                });

                //Prevent HTML5 video from being downloaded (right-click saved)?
                $('#mainVideo').bind('contextmenu', function () {
                    return false;
                });

                addCloseButtonInVideo(<?php echo json_encode(!empty($_REQUEST['forceCloseButton'])); ?>);
            });

            function compare(a, b, type) {
                console.log(a);
                console.log(b);
                console.log(type);
                switch (type) {
                    case "titleAZ":
                        return strcasecmp(a.name, b.name);
                        break;
                    case "titleZA":
                        return strcasecmp(b.name, a.name);
                        break;
                    case "newest":
                        return b.created > a.created ? 1 : (b.created < a.created ? -1 : 0);
                        break;
                    case "oldest":
                        return a.created > b.created ? 1 : (a.created < b.created ? -1 : 0);
                        break;
                    case "popular":
                        return a.likes > b.likes ? 1 : (b.likes < a.likes ? -1 : 0);
                        break;
                    case "views_count":
                        return a.views > b.views ? 1 : (a.views < b.views ? -1 : 0);
                        break;
                    default:
                        return 0;
                        break;
                }
            }
            function strcasecmp(s1, s2) {
                s1 = (s1 + '').toLowerCase();
                s2 = (s2 + '').toLowerCase();
                return s1 > s2 ? 1 : (s1 < s2 ? -1 : 0);
            }
        </script>
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>

    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>
