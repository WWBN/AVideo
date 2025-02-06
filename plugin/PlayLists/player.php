<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

AVideoPlugin::loadPlugin('PlayLists');

$isSerie = 1;
$isPlayList = true;

$plp = new PlayListPlayer(@$_GET['playlists_id'], @$_GET['tags_id'], true);
if (!$plp->canSee()) {
    forbiddenPage(_('You cannot see this playlist') . ' ' . basename(__FILE__) . ' ' . implode(', ', $plp->canNotSeeReason()));
}
$playListData = $plp->getPlayListData();

$video = $plp->getCurrentVideo();

$playlist_index = $plp->getIndex();

if (empty($playListData)) {
    videoNotFound('playListData not found');
}

$name = $plp->getName();


$_page = new Page(array($name));

$_page->setExtraStyles(
    array(
        'node_modules/video.js/dist/video-js.min.css',
        'node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.css'
    )
);
$_page->setExtraScripts(
    array(
        //'view/js/BootstrapMenu.min.js',
        'node_modules/videojs-playlist/dist/videojs-playlist.min.js',
        'node_modules/videojs-playlist-ui/dist/videojs-playlist-ui.min.js',
        'node_modules/videojs-youtube/dist/Youtube.min.js',
    )
);
?>
<style>
    .next-button:before {
        -moz-osx-font-smoothing: grayscale;
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        content: "\f051";
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
    }

    .video-js .next-button {
        width: 2em !important;
    }
</style>
<?php
if (!empty($advancedCustomUser->showChannelBannerOnModeYoutube)) {
?>
    <div class="container" style="margin-bottom: 10px;">
        <img src="<?php echo User::getBackground($video['users_id']); ?>" class="img img-responsive" />
    </div>
<?php
}
?>
<div class="container-fluid principalContainer" style="overflow: hidden;">
    <?php
    if (!empty($advancedCustom->showAdsenseBannerOnTop)) {
    ?>
        <style>
            .compress {
                top: 100px !important;
            }
        </style>
        <div class="row">
            <div class="col-lg-12 col-sm-12 col-xs-12 text-center">
                <?php
                echo getAdsLeaderBoardTop();
                ?>
            </div>
        </div>
    <?php
    }
    ?>
    <!-- playlist player -->
    <?php
    echo PlayerSkins::getMediaTag($video['filename'], $htmlMediaTag);
    ?>
    <!-- playlist player END -->

    <div class="row" id="modeYoutubeBottom">
        <div class="col-sm-1 col-md-1"></div>
        <div class="col-sm-8 col-md-8" id="modeYoutubeBottomContent">
        </div>
        <div class="col-sm-2 col-md-2 bgWhite list-group-item rightBar">
            <div class="col-lg-12 col-sm-12 col-xs-12">
                <?php echo getAdsSideRectangle(); ?>
            </div>
            <input type="search" id="playListSearch" class="form-control" placeholder=" <?php echo __("Search"); ?>" />
            <select class="form-control" id="embededSortBy">
                <option value="default"> <?php echo __("Default"); ?></option>
                <option value="titleAZ" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Title (A-Z)"); ?></option>
                <option value="titleZA" data-icon="glyphicon-sort-by-attributes-alt"> <?php echo __("Title (Z-A)"); ?></option>
                <option value="newest" data-icon="glyphicon-sort-by-attributes"> <?php echo __("Date added (newest)"); ?></option>
                <option value="oldest" data-icon="glyphicon-sort-by-attributes-alt"> <?php echo __("Date added (oldest)"); ?></option>
                <option value="popular" data-icon="glyphicon-thumbs-up"> <?php echo __("Most popular"); ?></option>
                <?php
                if (empty($advancedCustom->doNotDisplayViews)) {
                ?>
                    <option value="views_count" data-icon="glyphicon-eye-open" <?php echo (!empty($_POST['sort']['views_count'])) ? "selected='selected'" : "" ?>> <?php echo __("Most watched"); ?></option>
                <?php } ?>
            </select>
            <!-- <?php echo basename(__FILE__); ?> -->
            <div class="vjs-playlist" style="" id="playList">
                <!--
                          The contents of this element will be filled based on the
                          currently loaded playlist
                        -->
            </div>
        </div>
        <div class="col-sm-1 col-md-1"></div>
    </div>
</div>
<?php
include $global['systemRootPath'] . 'view/include/video.min.js.php';
echo AVideoPlugin::afterVideoJS();
?>
<script>
    var playerPlaylist = <?php echo json_encode($playListData); ?>;
    var originalPlayerPlaylist = playerPlaylist;

    if (typeof player === 'undefined' && $('#mainVideo').length) {
        player = videojs('mainVideo'
            <?php echo PlayerSkins::getDataSetup(); ?>);
    }

    var videos_id = playerPlaylist[0].videos_id;

    player.on('play', function() {
        addView(videos_id, this.currentTime());
    });

    player.on('timeupdate', function() {
        var time = Math.round(this.currentTime());
        if (time >= 5 && time % 5 === 0) {
            addView(videos_id, time);
        }
        if (this.liveTracker && this.liveTracker.atLiveEdge()) {
            // Reset speed to 1x when reaching the live edge
            if (this.playbackRate() !== 1) {
                this.playbackRate(1);
            }
        }
    });

    player.on('ended', function() {
        var time = Math.round(this.currentTime());
        addView(videos_id, time);
    });

    player.playlist(playerPlaylist);
    player.playlist.autoadvance(0);
    player.playlist.repeat(true);
    // Initialize the playlist-ui plugin with no option (i.e. the defaults).
    player.playlistUi();
    player.playlist.currentItem(<?php echo $playlist_index; ?>);

    var timeout;
    $(document).ready(function() {
        $("#playListSearch").keyup(function() {
            var filter = $(this).val();
            $(".vjs-playlist-item-list li").each(function() {
                if ($(this).find('.vjs-playlist-name').text().search(new RegExp(filter, "i")) < 0) {
                    $(this).slideUp();
                } else {
                    $(this).slideDown();
                }
            });
        });

        $('#embededSortBy').click(function() {
            setTimeout(function() {
                clearTimeout(timeout);
            }, 2000);
        });

        $('#embededSortBy').change(function() {
            var value = $(this).val();
            playerPlaylist.sort(function(a, b) {
                return compare(a, b, value);
            });
            player.playlist.sort(function(a, b) {
                return compare(a, b, value);
            });
        });

        //Prevent HTML5 video from being downloaded (right-click saved)?
        $('#mainVideo').bind('contextmenu', function() {
            return false;
        });

        console.log('currentTime player 1');
        player.currentTime(playerPlaylist[0].videoStartSeconds);
        $("#modeYoutubeBottomContent").load("<?php echo $global['webSiteRootURL']; ?>view/modeYoutubeBottom.php?videos_id=" + playerPlaylist[0].videos_id);
        $(".vjs-playlist-item ").click(function() {

        });

        player.on('playlistitem', function() {
            index = player.playlist.currentIndex();
            videos_id = playerPlaylist[index].videos_id;
            $("#modeYoutubeBottomContent").load("<?php echo $global['webSiteRootURL']; ?>view/modeYoutubeBottom.php?videos_id=" + playerPlaylist[index].videos_id);
            if (playerPlaylist[index] && playerPlaylist[index].videoStartSeconds) {
                setTimeout(function() {
                    console.log('currentTime player 2');
                    player.currentTime(playerPlaylist[index].videoStartSeconds);
                }, 500);
            }
            if (typeof enableDownloadProtection === 'function') {
                enableDownloadProtection();
            }
        });
        setTimeout(function() {
            if (typeof player == 'undefined') {
                player = videojs(videoJsId);
            }

            var Button = videojs.getComponent('Button');

            class NextButton extends Button {
                constructor() {
                    super(...arguments);
                    this.addClass('next-button');
                    this.addClass('vjs-button-fa-size');
                    this.controlText("Next");
                }
                handleClick() {
                    document.location = autoPlayVideoURL;
                }
            }

            // Register the new component
            videojs.registerComponent('NextButton', NextButton);
            player.getChild('controlBar').addChild('NextButton', {}, getPlayerButtonIndex('PlayToggle') + 1);
        }, 30);

    });

    function compare(a, b, type) {
        console.log(type);
        switch (type) {
            case "titleAZ":
                return strcasecmp(a.name, b.name);
                break;
            case "titleZA":
                return strcasecmp(b.name, a.name);
                break;
            case "newest":
                return a.created > b.created ? 1 : (a.created < b.created ? -1 : 0);
                break;
            case "oldest":
                return b.created > a.created ? 1 : (b.created < a.created ? -1 : 0);
                break;
            case "popular":
                return a.likes > b.likes ? 1 : (a.likes < b.likes ? -1 : 0);
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
$_page->print();
?>
