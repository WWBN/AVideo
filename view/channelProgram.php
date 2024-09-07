<?php
global $global, $config, $isChannel;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (empty($_GET['program_id'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
} else {
    $program = new PlayList($_GET['program_id']);
    $_GET['user_id'] = $program->getUsers_id();
}
$user_id = $_GET['user_id'];

$publicOnly = true;
$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $publicOnly = false;
    $isMyChannel = true;
}

$programs = PlayList::getAllFromUser(empty($_GET['program_id']) ? $user_id : 0, $publicOnly, false, @$_GET['program_id']);
if (empty($programs)) {
    $programs = PlayList::getAllFromUser($user_id, $publicOnly);
} else {
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_GET['program_id']);
    $videos_id = @$videosArrayId[0];
}
$playListsObj = AVideoPlugin::getObjectData("PlayLists");
//var_dump($_GET['program_id'], $videosArrayId, $programs);exit;
PlayLists::loadScripts();


$_page = new Page(array("Program"));
$_page->setExtraStyles(
    array(
        'node_modules/video.js/dist/video-js.min.css',
        'plugin/Gallery/style.css'
    )
);

?>
<style>
    .galleryVideo .panel {
        border-color: transparent;
        box-shadow: none;
    }

    .galleryVideo .panel .panel-body {
        padding: 5px;
    }
</style>
<div class="container-fluid gallery">
    <?php
    $channelName = @$_GET['channelName'];
    unset($_GET['channelName']);
    $startC = microtime(true);
    foreach ($programs as $key => $program) {
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        $videosArrayId = PlayList::getVideosIdFromPlaylist($program['id']);
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        //getAllVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
        if (empty($videosArrayId) && ($program['status'] == "favorite" || $program['status'] == "watch_later")) {
            unset($programs[$key]);
            continue;
        } elseif (empty($videosArrayId)) {
            $videosP = [];
        } else {
            $videosP = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, true, $videosArrayId, false, true);
        } //var_dump($videosArrayId, $videosP);exit;
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        //_error_log("channelPlaylist videosP: ".json_encode($videosP));
        //$videosP = PlayList::sortVideos($videosP, $videosArrayId);
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        //_error_log("channelPlaylist videosP2: ".json_encode($videosP));
        //_error_log("channelPlaylist videosArrayId: ".json_encode($videosArrayId));
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        $totalVideos = count($videosP);

        $checked = '';
        if (!empty($program['showOnFirstPage'])) {
            $checked = ' checked="checked" ';
        }
    ?>
        <br>
        <!-- channelProgram -->
        <div class="panel panel-default program" playListId="<?php echo $program['id']; ?>">
            <div class="panel-heading clearfix" style="padding-left: 10px;">
                <span class="badge pull-right"><?php echo $totalVideos; ?> <?php echo __('Videos'); ?></span>
                <div class="pull-left">
                    <strong style="font-size: 1.1em;" class="playlistName">
                        <!-- <?php echo basename(__FILE__); ?> -->
                        <?php echo __($program['name']); ?>
                    </strong><br>
                    <small class="text-muted">
                        <?php echo seconds2human(PlayList::getTotalDurationFromPlaylistInSeconds($program['id'])); ?>
                    </small>
                </div>
                <?php
                PlayLists::getPLButtons($program['id'], false);
                if (PlayLists::canManageAllPlaylists()) {
                ?>
                    <br>
                    <div class="pull-right" style="padding: 2px 0 0 0;">
                        <label for="addOnFirstPage<?php echo $program['id']; ?>">
                            <span style="margin-right: 10px;"><?php echo __('Add to first page'); ?></span>
                        </label>
                        <div class="material-small material-switch pull-right">
                            <input <?php echo $checked; ?> name="addOnFirstPage" id="addOnFirstPage<?php echo $program['id']; ?>" class="addOnFirstPage" type="checkbox" value="<?php echo $program['id']; ?>">
                            <label for="addOnFirstPage<?php echo $program['id']; ?>" class="label-success"></label>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>

            <?php
            if (!empty($videosArrayId)) {
            ?>

                <div class="panel-body">
                    <?php
                    $_REQUEST['user_id'] = $program['users_id'];
                    $_REQUEST['playlists_id'] = $program['id'];
                    include $global['systemRootPath'] . 'plugin/PlayLists/epg.html.php';
                    ?>
                    <div id="sortable<?php echo $program['id']; ?>" style="list-style: none;">
                        <?php
                        $count = 0;
                        $realCount = 0;
                        foreach ($videosP as $value) {
                            $episodeLink = PlayLists::getURL($program['id'], $count, $value["channelName"], $program['name'], $value['clean_title']);
                            $count++;
                            if (empty($value['created'])) {
                                continue;
                            }
                            $realCount++;
                            $name = User::getNameIdentificationById($value['users_id']);

                            $class = '';
                            $style = '';
                            if ($count > 6) {
                                $class = "showMoreLess{$program['id']}";
                                $style = "display: none;";
                            }
                        ?>
                            <li class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo showMoreLess <?php echo $class; ?> " id="<?php echo $value['id']; ?>" style="padding: 1px;  <?php echo $style; ?>">
                                <div class="panel panel-default" playListId="<?php echo $program['id']; ?>" style="min-height: 215px;">
                                    <div class="panel-body" style="overflow: hidden;">
                                        <?php
                                        echo Video::getVideoImagewithHoverAnimationFromVideosId($value);
                                        ?>
                                        <a class="h6 galleryLink hrefLink" href="<?php echo $episodeLink; ?>" title="<?php echo getSEOTitle($value['title']); ?>">
                                            <strong class="title"><?php echo Video::$statusIcons[$value['status']]; ?> <?php echo getSEOTitle($value['title']); ?></strong>
                                        </a>
                                        <div class="galeryDetails" style="min-height: 60px;">
                                            <div class="galleryTags">
                                                <?php
                                                $value['tags'] = Video::getTags($value['id']);
                                                foreach ($value['tags'] as $value2) {
                                                    if (is_array($value2)) {
                                                        $value2 = (object) $value2;
                                                    }
                                                    if ($value2->label === __("Group")) {
                                                ?>
                                                        <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                            if (empty($advancedCustom->doNotDisplayViews)) {
                                            ?>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                            <?php }

                                            if (!empty($advancedCustom->showCreationTimeOnVideoItem)) {
                                            ?>
                                                <div>
                                                    <i class="far fa-clock"></i>
                                                    <?php echo humanTiming(strtotime($value['videoCreation']), 0, true, true); ?>
                                                </div>
                                            <?php
                                            }else{
                                                echo '<!-- empty showCreationTimeOnVideoItem '.basename(__FILE__).' line='.__LINE__.'-->';
                                            }
                                            if (!empty($advancedCustom->showChannelNameOnVideoItem)) {
                                            ?>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <?php echo $name; ?>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <?php
                                            if (Video::canEdit($value['id'])) {
                                            ?>
                                                <div>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>

                                                </div>
                                            <?php }
                                            ?>
                                            <?php
                                            if ($isMyChannel) {
                                            ?>
                                                <div>
                                                    <span style=" cursor: pointer;" class="btn-link text-primary removeVideo" playlist_id="<?php echo $program['id']; ?>" video_id="<?php echo $value['id']; ?>">
                                                        <i class="fa fa-trash"></i> <?php echo __("Remove"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span playlist_id="<?php echo $program['id']; ?>" video_id="<?php echo $value['id']; ?>">
                                                        <i class="fas fa-sort-numeric-down"></i> <?php echo __("Sort"); ?>
                                                        <input type="number" step="1" class="video_order" value="<?php echo empty($program['videos'][$count - 1]) ? 0 : intval(@$program['videos'][$count - 1]['video_order']); ?>" style="max-width: 50px;">
                                                        <button class="btn btn-sm btn-xs sortNow"><i class="fas fa-check-square"></i></button>
                                                    </span>
                                                </div>
                                            <?php }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php
                            if ($realCount % 6 === 0) {
                                echo '<div class="clearfix hidden-md hidden-sm hidden-xs"></div>';
                            }
                            if ($realCount % 3 === 0) {
                                echo '<div class="clearfix hidden-lg hidden-xs"></div>';
                            }
                            if ($realCount % 2 === 0) {
                                echo '<div class="clearfix hidden-md hidden-sm hidden-lg"></div>';
                            }
                        }
                        ?>
                    </div>
                </div>

                <div class="panel-footer">
                    <?php
                    if (count($programs) > 1) {
                    ?>
                        <button class="btn btn-default btn-xs btn-sm showMoreLessBtn showMoreLessBtn<?php echo $program['id']; ?>" onclick="$('.showMoreLessBtn<?php echo $program['id']; ?>').toggle();
                                        $('.<?php echo $class; ?>').slideDown();"><i class="fas fa-angle-down"></i> <?php echo __('Show More'); ?></button>
                        <button class="btn btn-default btn-xs btn-sm  showMoreLessBtn showMoreLessBtn<?php echo $program['id']; ?>" onclick="$('.showMoreLessBtn<?php echo $program['id']; ?>').toggle();
                                        $('.<?php echo $class; ?>').slideUp();" style="display: none;"><i class="fas fa-angle-up"></i> <?php echo __('Show Less'); ?></button>
                    <?php
                    }
                    if ($isMyChannel && !empty($videosArrayId)) {
                    ?>
                        <span class="label label-info"><i class="fa fa-info-circle"></i> <?php echo __("Drag and drop to sort"); ?></span>
                    <?php }
                    ?>
                </div>
            <?php }
            ?>

        </div>
    <?php
    }

    $_GET['channelName'] = $channelName;
    ?>

    <script>
        var timoutembed;

        function setTextEmbedCopied() {
            clearTimeout(timoutembed);
            $("#btnEmbedText").html("<?php echo __("Copied!"); ?>");
            timoutembed = setTimeout(function() {
                $("#btnEmbedText").html("<?php echo __("Copy embed code"); ?>");
            }, 3000);
        }

        function saveSortable($sortableObject, playlist_id) {
            var list = $($sortableObject).sortable("toArray");
            $.ajax({
                url: webSiteRootURL+'objects/playlistSort.php',
                data: {
                    "list": list,
                    "playlist_id": playlist_id
                },
                type: 'post',
                success: function(response) {
                    //$("#channelPlaylists").load(webSiteRootURL + "view/channelPlaylist.php?channelName=" + channelName);
                    modal.hidePleaseWait();
                }
            });
        }

        function sortNow($t, position) {
            var $this = $($t).closest('.galleryVideo');
            var $uiDiv = $($t).closest('.ui-sortable');
            var $playListId = $($t).closest('.panel').attr('playListId');
            var $list = $($t).closest('.ui-sortable').find('li');
            if (position < 0) {
                return false;
            }
            if (position === 0) {
                $this.slideUp(500, function() {
                    $this.insertBefore($this.siblings(':eq(0)'));
                    saveSortable($uiDiv, $playListId);
                }).slideDown(500);
            } else if ($list.length - 1 > position) {
                $this.slideUp(500, function() {
                    $this.insertBefore($this.siblings(':eq(' + position + ')'));
                    saveSortable($uiDiv, $playListId);
                }).slideDown(500);
            } else {
                $this.slideUp(500, function() {
                    $this.insertAfter($this.siblings(':eq(' + ($list.length - 2) + ')'));
                    saveSortable($uiDiv, $playListId);
                }).slideDown(500);
            }
        }

        var currentObject;
        $(function() {
            $('.addOnFirstPage').on('change', function() {
                url = webSiteRootURL + 'objects/playlistAddOnFirstPage.json.php';
                var playlist_id = $(this).val();
                var showOnFirstPage = $(this).prop('checked');
                avideoAjax(url, {
                    playlist_id: playlist_id,
                    showOnFirstPage: showOnFirstPage
                });
            });
            <?php
            if (count($programs) <= 1 || !empty($palyListsObj->expandPlayListOnChannels)) {
            ?>
                $('.showMoreLess').slideDown();
                $('.showMoreLessBtn').toggle();
            <?php
            }
            ?>
            $('.removeVideo').click(function() {
                currentObject = this;

                swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then(function(willDelete) {
                        if (willDelete) {

                            modal.showPleaseWait();
                            var playlist_id = $(currentObject).attr('playlist_id');
                            var video_id = $(currentObject).attr('video_id');
                            $.ajax({
                                url: webSiteRootURL + 'objects/playlistRemoveVideo.php',
                                data: {
                                    "playlist_id": playlist_id,
                                    "video_id": video_id
                                },
                                type: 'post',
                                success: function(response) {
                                    reloadPlayLists();
                                    $(".playListsIds" + video_id).prop("checked", false);
                                    $(currentObject).closest('.galleryVideo').fadeOut();
                                    modal.hidePleaseWait();
                                }
                            });
                        }
                    });

            });

            $('.deletePlaylist').click(function() {
                currentObject = this;

                swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then(function(willDelete) {
                        if (willDelete) {

                            modal.showPleaseWait();
                            var playlist_id = $(currentObject).attr('playlist_id');
                            console.log(playlist_id);
                            $.ajax({
                                url: webSiteRootURL+'objects/playlistRemove.php',
                                data: {
                                    "playlist_id": playlist_id
                                },
                                type: 'post',
                                success: function(response) {
                                    $(currentObject).closest('.panel').slideUp();
                                    modal.hidePleaseWait();
                                }
                            });
                        }
                    });

            });

            $('.statusPlaylist').click(function() {
                var playlist_id = $(this).attr('playlist_id');
                var status = "public";
                if ($('#statusPrivate' + playlist_id).is(":visible")) {
                    status = "public";
                    $('.statusPlaylist' + playlist_id + ' span').hide();
                    $('#statusPublic' + playlist_id).fadeIn();
                } else if ($('#statusPublic' + playlist_id).is(":visible")) {
                    status = "unlisted";
                    $('.statusPlaylist' + playlist_id + ' span').hide();
                    $('#statusUnlisted' + playlist_id).fadeIn();
                } else if ($('#statusUnlisted' + playlist_id).is(":visible")) {
                    status = "private";
                    $('.statusPlaylist' + playlist_id + ' span').hide();
                    $('#statusPrivate' + playlist_id).fadeIn();
                }
                modal.showPleaseWait();
                console.log(playlist_id);
                $.ajax({
                    url: webSiteRootURL+'objects/playlistStatus.php',
                    data: {
                        "playlist_id": playlist_id,
                        "status": status
                    },
                    type: 'post',
                    success: function(response) {

                        modal.hidePleaseWait();
                    }
                });

            });

            $('.renamePlaylist').click(function() {
                currentObject = this;
                swal({
                    text: "<?php echo __("Change Playlist Name"); ?>!",
                    content: "input",
                    button: {
                        text: "<?php echo __("Confirm Playlist name"); ?>",
                        closeModal: false,
                    },
                }).then(function(name) {
                    if (!name)
                        throw null;
                    modal.showPleaseWait();
                    var playlist_id = $(currentObject).attr('playlist_id');
                    console.log(playlist_id);
                    return fetch('<?php echo $global['webSiteRootURL']; ?>objects/playlistRename.php?playlist_id=' + playlist_id + '&name=' + encodeURI(name));
                }).then(function(results) {
                    return results.json();
                }).then(function(response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                        modal.hidePleaseWait();
                    } else {
                        $(currentObject).closest('.panel').find('.playlistName').text(response.name);
                        swal.stopLoading();
                        swal.close();
                        modal.hidePleaseWait();
                    }
                }).catch(function(err) {
                    if (err) {
                        swal("Oh noes!", "The AJAX request failed!", "error");
                    } else {
                        swal.stopLoading();
                        swal.close();
                    }
                    modal.hidePleaseWait();
                });;

            });

            $('.sortNow').click(function() {
                var $val = $(this).siblings("input").val();
                sortNow(this, $val);
            });

            $('.video_order').keypress(function(e) {
                if (e.which == 13) {
                    sortNow(this, $(this).val());
                }
            });

        });
    </script>
    <!--
            channelPlaylist
            <?php
            $timesC[__LINE__] = microtime(true) - $startC;
            $startC = microtime(true);
            foreach ($timesC as $key => $value) {
                echo "Line: {$key} -> {$value}\n";
            }
            ?>
            -->
</div><!--/.container-->

<?php
$_page->print();
?>