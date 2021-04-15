<?php
global $global, $config;
global $isEmbed;
$isEmbed = 1;
$isPlayList = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
$objSecure = AVideoPlugin::loadPluginIfEnabled('SecureVideosDirectory');
if (!empty($objSecure)) {
    $objSecure->verifyEmbedSecurity();
}

require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/PlayLists/PlayListElement.php';

if (!User::isAdmin() && !PlayList::canSee($_GET['playlists_id'], User::getId())) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$playlist_index = intval(@$_REQUEST['playlist_index']);

$pl = new PlayList($_GET['playlists_id']);

$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

$playListData = array();
$collectionsList = PlayList::showPlayListSelector($playList);
$videoStartSeconds = array();

$users_id = User::getId();

foreach ($playList as $key => $value) {
    $oldValue = $value;

    if (!User::isAdmin() && !Video::userGroupAndVideoGroupMatch($users_id, $value['videos_id'])) {
        unset($playList[$key]);
        continue;
    }

    if ($oldValue['type'] === 'serie' && !empty($oldValue['serie_playlists_id'])) {
        $subPlayList = PlayList::getVideosFromPlaylist($value['serie_playlists_id']);
        foreach ($subPlayList as $value) {
            $sources = getVideosURL($value['filename']);
            $images = Video::getImageFromFilename($value['filename'], $value['type']);
            $externalOptions = _json_decode($value['externalOptions']);

            $src = new stdClass();
            $src->src = $images->thumbsJpg;
            $thumbnail = array();
            $thumbnail[] = $src;

            $playListSources = array();
            foreach ($sources as $value2) {
                if ($value2['type'] !== 'video' && $value2['type'] !== 'audio') {
                    continue;
                }
                $playListSources[] = new playListSource($value2['url']);
            }
            if (empty($playListSources)) {
                continue;
            }
            if (User::isLogged()) {
                $videoStartSeconds = Video::getLastVideoTimePosition($value['videos_id']);
            }

            if (empty($videoStartSeconds)) {
                $videoStartSeconds = parseDurationToSeconds(@$externalOptions->videoStartSeconds);
            }

            $playListData[] = new PlayListElement($value['title'], $value['description'], $value['duration'], $playListSources, $thumbnail, $images->poster, $videoStartSeconds, $value['cre'], $value['likes'], $value['views_count'], $value['videos_id'], "embedPlayList subPlaylistCollection-{$oldValue['serie_playlists_id']}");
        }
    } else {
        $sources = getVideosURL($value['filename']);
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $externalOptions = _json_decode($value['externalOptions']);

        $src = new stdClass();
        $src->src = $images->thumbsJpg;
        $thumbnail = array();
        $thumbnail[] = $src;

        $playListSources = array();
        foreach ($sources as $value2) {
            if ($value2['type'] !== 'video' && $value2['type'] !== 'audio') {
                continue;
            }
            $playListSources[] = new playListSource($value2['url']);
        }

        if (function_exists('getVTTTracks')) {
            $subtitleTracks = getVTTTracks($value['filename'], true);
        }

        if (empty($playListSources)) {
            continue;
        }

        if (User::isLogged()) {
            $videoStartSeconds = Video::getLastVideoTimePosition($value['videos_id']);
        }

        if (empty($videoStartSeconds)) {
            $videoStartSeconds = parseDurationToSeconds(@$externalOptions->videoStartSeconds);
        }
        $playListData[] = new PlayListElement($value['title'], $value['description'], $value['duration'], $playListSources, $thumbnail, $images->poster, $videoStartSeconds, $value['cre'], $value['likes'], $value['views_count'], $value['videos_id'], "embedPlayList ", $subtitleTracks);
    }
}

if (empty($playListData)) {
    forbiddenPage(__("The program is empty"));
}

$url = PlayLists::getLink($pl->getId());
$title = $pl->getName();

if ($serie = PlayLists::isPlayListASerie($pl->getId())) {
    setVideos_id($serie['id']);
} else if (!empty($playList[$playlist_index])) {
    setVideos_id($playList[$playlist_index]['id']);
}
//var_dump($playListData);exit;
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>

        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="view/img/favicon.ico">
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo getCDN(); ?>view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo getCDN(); ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo getCDN(); ?>view/css/fontawesome-free-5.5.0-web/css/all.min.css" rel="stylesheet" type="text/css"/>

        <link href="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.css" rel="stylesheet">

        <script src="<?php echo getCDN(); ?>view/js/jquery-3.5.1.min.js" type="text/javascript"></script>
        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <?php
        echo AVideoPlugin::getHeadCode();
        ?>
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
        </style>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <video style="width: 100%; height: 100%;" playsinline
        <?php if ($config->getAutoplay() && false) { // disable it for now     ?>
                   autoplay="true"
                   muted="muted"
               <?php } ?>
               preload="auto"
               controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo">
        </video>
        <div style="display: none;" id="playListHolder">
            <div id="playListFilters">
                <?php
                if (!empty($collectionsList)) {
                    ?>
                    <select class="form-control" id="subPlaylistsCollection" >
                        <option value="0"> <?php echo __("Show all"); ?></option>
                        <?php
                        foreach ($collectionsList as $value) {
                            echo '<option value="' . $value['serie_playlists_id'] . '">' . $value['title'] . '</option>';
                        }
                        ?>
                    </select>
                    <?php
                }
                ?>
                <input type="search" id="playListSearch" class="form-control" placeholder=" <?php echo __("Search"); ?>"/>
                <select class="form-control" id="embededSortBy" >
                    <option value="default"> <?php echo __("Sort"); ?></option>
                    <option value="titleAZ" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Title (A-Z)"); ?></option>
                    <option value="titleZA" data-icon="glyphicon-sort-by-attributes-alt"> <?php echo __("Title (Z-A)"); ?></option>
                    <option value="newest" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Date added (newest)"); ?></option>
                    <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt" > <?php echo __("Date added (oldest)"); ?></option>
                    <option value="popular" data-icon="glyphicon-thumbs-up"> <?php echo __("Most popular"); ?></option>
                    <?php
                    if (empty($advancedCustom->doNotDisplayViews)) {
                        ?> 
                        <option value="views_count" data-icon="glyphicon-eye-open"  <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="vjs-playlist" style="" id="playList">
                <!--
                  The contents of this element will be filled based on the
                  currently loaded playlist
                -->
            </div>
        </div>

        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <?php
        $jsFiles = array();
        $jsFiles[] = "view/js/BootstrapMenu.min.js";
        $jsFiles[] = "view/js/seetalert/sweetalert.min.js";
        $jsFiles[] = "view/js/bootpag/jquery.bootpag.min.js";
        $jsFiles[] = "view/js/bootgrid/jquery.bootgrid.js";
        $jsFiles[] = "view/bootstrap/bootstrapSelectPicker/js/bootstrap-select.min.js";
        $jsFiles[] = "view/js/script.js";
        $jsFiles[] = "view/js/js-cookie/js.cookie.js";
        $jsFiles[] = "view/css/flagstrap/js/jquery.flagstrap.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.min.js";
        $jsFiles[] = "view/js/jquery.lazy/jquery.lazy.plugins.min.js";
        $jsFiles[] = "view/js/jquery-ui/jquery-ui.min.js";
        $jsFiles[] = "view/js/jquery-toast/jquery.toast.min.js";
        $jsFiles[] = "view/bootstrap/js/bootstrap.min.js";
        $jsURL = combineFiles($jsFiles, "js");
        ?>
        <script src="<?php echo getCDN(); ?>view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        ?>
        <script src="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist/videojs-playlist.js"></script>
        <script src="<?php echo getCDN(); ?>plugin/PlayLists/videojs-playlist-ui/videojs-playlist-ui.js"></script>
        <script>
            var embed_playerPlaylist = <?php echo json_encode($playListData); ?>;
            var originalPlayerPlaylist = embed_playerPlaylist;
            var updatePLSourcesTimeout;
            function updatePLSources(_index) {
                if (_index < 0) {
                    _index = 0;
                }
                clearTimeout(updatePLSourcesTimeout);
                if (typeof player.updateSrc == 'function' && typeof player.videoJsResolutionSwitcher != 'undefined' && typeof embed_playerPlaylist[_index] != 'undefined' && typeof embed_playerPlaylist[_index].sources != 'undefined') {
                    console.log('updatePLSources', _index);
                    //player.src(embed_playerPlaylist[_index].sources);
                    player.updateSrc(embed_playerPlaylist[_index].sources);
                    player.currentTime(embed_playerPlaylist[_index].videoStartSeconds);
                    //player.currentResolution(embed_playerPlaylist[_index].sources[0].label);
                    //player.currentResolution(embed_playerPlaylist[_index].sources[0].label);
                    //player.updateSrc(embed_playerPlaylist[_index].sources);
                    userIsControling = false;
                    reloadAds();

                    if (typeof embed_playerPlaylist[_index] !== 'undefined') {
                        updatePLSourcesTimeout = setTimeout(function () {
                            playerPlay(embed_playerPlaylist[_index].videoStartSeconds);
                            if(embed_playerPlaylist[_index].tracks && embed_playerPlaylist[_index].tracks.length){
                                var _tracks = embed_playerPlaylist[_index].tracks;
                                setTimeout(function () {
                                    for (let j = 0; j < _tracks.length; j++) {
                                        console.log('tracks ',_tracks[j]);
                                        player.addRemoteTextTrack({kind: 'captions',label:_tracks[j].label,src: _tracks[j].src }, false);
                                    }
                                }, 1000);
                            }

                        }, 1000);
                    }
                } else {
                    updatePLSourcesTimeout = setTimeout(function () {
                        updatePLSources(_index);
                    }, 500);
                    return false;
                }
            }

<?php
$str = "
            player.playlist(embed_playerPlaylist);
            player.playlist.autoadvance(0);
            player.on('play', function () {
                addView(embed_playerPlaylist[player.playlist.currentIndex()].videos_id, 0);
            });
            player.on('ended', function(){ 
                embed_playerPlaylist[player.playlist.currentIndex()].videoStartSeconds = 0;
            });
            player.on('timeupdate', function () {
                var time = Math.round(player.currentTime());
                if (time >= 5) {
                    embed_playerPlaylist[player.playlist.currentIndex()].videoStartSeconds = time;
                    if (time % 5 === 0) {
                        addView(embed_playerPlaylist[player.playlist.currentIndex()].videos_id, time);
                    }
                }
                
            });
            player.on('playlistchange', function() {
                console.log('event playlistchange');
            });
            player.on('duringplaylistchange', function() {
                console.log('event duringplaylistchange');
            });
            player.on('playlistitem', function() {
                var index = player.playlist.currentIndex();
                console.log('event playlistitem '+index);
                updatePLSources(index);
            });
            player.playlistUi();";
if (!empty($playlist_index)) {
    $str .= 'player.playlist.currentItem(' . $playlist_index . ');';
}
$str .= "if (typeof embed_playerPlaylist[0] !== 'undefined') {
                    updatePLSources({$playlist_index});
                }
                $('.vjs-playlist-item ').click(function () {
                    var index = player.playlist.currentIndex();
                    updatePLSources(index);
                });";
PlayerSkins::getStartPlayerJS($str);
?>
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
        <script>
            var topInfoTimeout;
            $(document).ready(function () {
                setInterval(function () {
                    if (typeof player !== 'undefined') {
                        if (!player.paused() && (!player.userActive() || !$('.vjs-control-bar').is(":visible") || $('.vjs-control-bar').css('opacity') == "0")) {
                            $('#topInfo').fadeOut();
                        } else {
                            $('#topInfo').fadeIn();
                        }
                    }
                }, 200);

                $("iframe, #topInfo").mouseover(function (e) {
                    clearTimeout(topInfoTimeout);
                    $('#mainVideo').addClass("vjs-user-active");
                    topInfoTimeout = setTimeout(function () {
                        $('#mainVideo').removeClass("vjs-user-active");
                    }, 5000);
                });

                $("iframe").mouseout(function (e) {
                    topInfoTimeout = setTimeout(function () {
                        $('#mainVideo').removeClass("vjs-user-active");
                    }, 500);
                });

            });
        </script>
        <?php
        echo AVideoPlugin::getFooterCode();
        ?>

    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>