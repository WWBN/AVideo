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

if (!PlayList::canSee($_GET['playlists_id'], User::getId())) {
    die('{"error":"' . __("Permission denied") . '"}');
}

$playList = PlayList::getVideosFromPlaylist($_GET['playlists_id']);

$playListData = array();
$videoStartSeconds = array();
foreach ($playList as $value) {

    $sources = getVideosURL($value['filename']);
    $images = Video::getImageFromFilename($value['filename'], $value['type']);
    $externalOptions = json_decode($value['externalOptions']);

    $src = new stdClass();
    $src->src = $images->thumbsJpg;
    $thumbnail = array($src);

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
    $playListData[] = new PlayListElement($value['title'], $value['description'], $value['duration'], $playListSources, $thumbnail, $images->poster, parseDurationToSeconds(@$externalOptions->videoStartSeconds), $value['cre'], $value['likes'], $value['views_count'], $value['videos_id']);
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
        <?php if ($config->getAutoplay() && false) { // disable it for now    ?>
                   autoplay="true"
                   muted="muted"
               <?php } ?>
               preload="auto"
               controls class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered" id="mainVideo">
        </video>
        <button class="btn btn-sm btn-xs btn-default" style="position: absolute; top: 5px; right:35%;" id="closeButton"><i class="fas fa-times-circle"></i></button>
        <div style="position: absolute; right: 0; top: 0; width: 35%; height: 100%; overflow-y: scroll; margin-right: 0; " id="playListHolder">
            <input type="search" id="playListSearch" class="form-control" placeholder=" <?php echo __("Search"); ?>"/>
            <select class="form-control" id="embededSortBy" >
                <option value="default"> <?php echo __("Default"); ?></option>
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

            var playerPlaylist = <?php echo json_encode($playListData); ?>;
            var originalPlayerPlaylist = playerPlaylist;

            player.playlist(playerPlaylist);
            player.playlist.autoadvance(0);
            // Initialize the playlist-ui plugin with no option (i.e. the defaults).
            player.playlistUi();
            var timeout;
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

                timeout = setTimeout(function () {
                    $('#playList, #embededSortBy, #playListSearch, #closeButton').fadeOut();
                }, 2000);
                $('#playListHolder').mouseenter(function () {
                    $('#playList, #embededSortBy, #playListSearch, #closeButton').fadeIn();
                    clearTimeout(timeout);
                });
                $('#playListHolder').mouseleave(function () {
                    timeout = setTimeout(function () {
                        $('#playList, #embededSortBy, #playListSearch, #closeButton').fadeOut();
                    }, 3000);
                });
                
                $('#closeButton').click(function () {
                    $('#playList, #embededSortBy, #playListSearch, #closeButton').fadeOut();
                });

                $('#embededSortBy').click(function () {
                    setTimeout(function () {
                        clearTimeout(timeout);
                    }, 2000);
                });

                $('#embededSortBy').change(function () {
                    var value = $(this).val();
                    playerPlaylist.sort(function (a, b) {
                        return compare(a, b, value);
                    });
                    player.playlist.sort(function (a, b) {
                        return compare(a, b, value);
                    });
                });

                //Prevent HTML5 video from being downloaded (right-click saved)?
                $('#mainVideo').bind('contextmenu', function () {
                    return false;
                });

                player.currentTime(playerPlaylist[0].videoStartSeconds);
                $(".vjs-playlist-item ").click(function () {
                    index = $(this).index();
                    setTimeout(function () {
                        player.currentTime(playerPlaylist[index].videoStartSeconds);
                    }, 500);

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
    </body>
</html>

<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>