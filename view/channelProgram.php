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

$programs = PlayList::getAllFromUser($user_id, $publicOnly, false, @$_GET['program_id']);
if (empty($programs)) {
    $programs = PlayList::getAllFromUser($user_id, $publicOnly);
} else {
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_GET['program_id']);
    $videos_id = @$videosArrayId[0];
}
$playListsObj = AVideoPlugin::getObjectData("PlayLists");
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("Program") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .galleryVideo .panel{
                border-color: transparent;
                box-shadow: none;
            }
            .galleryVideo .panel .panel-body {
                padding: 5px;
            }
        </style>
        <?php
        if (!empty($videos_id)) {
            getOpenGraph($videos_id);
        }
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
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
                //getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
                if (empty($videosArrayId) && ($program['status'] == "favorite" || $program['status'] == "watch_later")) {
                    unset($programs[$key]);
                    continue;
                } elseif (empty($videosArrayId)) {
                    $videosP = [];
                } else {
                    $videosP = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
                }//var_dump($videosArrayId, $videosP);exit;
                @$timesC[__LINE__] += microtime(true) - $startC;
                $startC = microtime(true);
                //_error_log("channelPlaylist videosP: ".json_encode($videosP));
                $videosP = PlayList::sortVideos($videosP, $videosArrayId);
                @$timesC[__LINE__] += microtime(true) - $startC;
                $startC = microtime(true);
                //_error_log("channelPlaylist videosP2: ".json_encode($videosP));
                //_error_log("channelPlaylist videosArrayId: ".json_encode($videosArrayId));
                @$timesC[__LINE__] += microtime(true) - $startC;
                $startC = microtime(true);

                ?>
                <br>
                <div class="panel panel-default program" playListId="<?php echo $program['id']; ?>">
                    <div class="panel-heading">
                        <strong class="playlistName"><?php echo $program['name']; ?> </strong>
                        <?php
                        PlayLists::getPLButtons($program['id'], false);
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
                                foreach ($videosP as $value) {
                                    $episodeLink = "{$global['webSiteRootURL']}program/{$program['id']}/{$count}";
                                    $count++;
                                    if (empty($value['created'])) {
                                        continue;
                                    }
                                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
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
                                                    <strong class="title"><?php echo getSEOTitle($value['title']); ?></strong>
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
                                                    ?>

                                                    <div>
                                                        <i class="far fa-clock"></i>
                                                        <?php echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago'); ?>
                                                    </div>
                                                    <div>
                                                        <i class="fa fa-user"></i>
                                                        <?php echo $name; ?>
                                                    </div>
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
                                                                <input type="number" step="1" class="video_order" value="<?php echo intval($program['videos'][$count - 1]['video_order']); ?>" style="max-width: 50px;">
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
                                    if ($count % 6 === 0) {
                                        echo '<div class="clearfix hidden-md hidden-sm hidden-xs"></div>';
                                    }
                                    if ($count % 3 === 0) {
                                        echo '<div class="clearfix hidden-lg hidden-xs"></div>';
                                    }
                                    if ($count % 2 === 0) {
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
                                <span class="label label-info" ><i class="fa fa-info-circle"></i> <?php echo __("Drag and drop to sort"); ?></span>
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

            <div class="modal fade" id="videoSearchModal" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">

                            <div class="panel panle-default">
                                <div class="panel-heading">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a data-toggle="tab" href="#addSeries"><i class="fas fa-list"></i> <?php echo __('Series'); ?></a></li>
                                        <li><a data-toggle="tab" href="#addVideos"><i class="fas fa-video"></i> <?php echo __('Videos'); ?></a></li>
                                    </ul>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div id="addSeries" class="tab-pane fade in active">
                                            <form id="serieSearch-form" name="search-form" action="<?php echo $global['webSiteRootURL'] . ''; ?>" method="get">
                                                <div id="custom-search-input">
                                                    <div class="input-group col-md-12">
                                                        <input type="search" name="searchPhrase" id="serieSearch-input" class="form-control input-lg" placeholder="<?php echo __('Search Serie'); ?>" value="">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-info btn-lg" type="submit">
                                                                <i class="fas fa-search"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </form>
                                            <hr>
                                            <div id="searchSerieResult"></div>
                                        </div>
                                        <div id="addVideos" class="tab-pane fade">
                                            <form id="videoSearch-form" name="search-form" action="<?php echo $global['webSiteRootURL'] . ''; ?>" method="get">
                                                <div id="custom-search-input">
                                                    <div class="input-group col-md-12">
                                                        <input type="search" name="searchPhrase" id="videoSearch-input" class="form-control input-lg" placeholder="<?php echo __('Search Videos'); ?>" value="">
                                                        <span class="input-group-btn">
                                                            <button class="btn btn-info btn-lg" type="submit">
                                                                <i class="fas fa-search"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </form>
                                            <hr>
                                            <div id="searchVideoResult"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>

                var timoutembed;
                function setTextEmbedCopied() {
                    clearTimeout(timoutembed);
                    $("#btnEmbedText").html("<?php echo __("Copied!"); ?>");
                    timoutembed = setTimeout(function () {
                        $("#btnEmbedText").html("<?php echo __("Copy embed code"); ?>");
                    }, 3000);
                }

                function saveSortable($sortableObject, playlist_id) {
                    var list = $($sortableObject).sortable("toArray");
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php',
                        data: {
                            "list": list,
                            "playlist_id": playlist_id
                        },
                        type: 'post',
                        success: function (response) {
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
                        $this.slideUp(500, function () {
                            $this.insertBefore($this.siblings(':eq(0)'));
                            saveSortable($uiDiv, $playListId);
                        }).slideDown(500);
                    } else if ($list.length - 1 > position) {
                        $this.slideUp(500, function () {
                            $this.insertBefore($this.siblings(':eq(' + position + ')'));
                            saveSortable($uiDiv, $playListId);
                        }).slideDown(500);
                    } else {
                        $this.slideUp(500, function () {
                            $this.insertAfter($this.siblings(':eq(' + ($list.length - 2) + ')'));
                            saveSortable($uiDiv, $playListId);
                        }).slideDown(500);
                    }
                }

                var currentObject;
                $(function () {
<?php
if (count($programs) <= 1 || !empty($palyListsObj->expandPlayListOnChannels)) {
    ?>
                        $('.showMoreLess').slideDown();
                        $('.showMoreLessBtn').toggle();
    <?php
}
?>
                    $('.removeVideo').click(function () {
                        currentObject = this;

                        swal({
                            title: "<?php echo __("Are you sure?"); ?>",
                            text: "<?php echo __("You will not be able to recover this action!"); ?>",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        })
                                .then(function (willDelete) {
                                    if (willDelete) {

                                        modal.showPleaseWait();
                                        var playlist_id = $(currentObject).attr('playlist_id');
                                        var video_id = $(currentObject).attr('video_id');
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistRemoveVideo.php',
                                            data: {
                                                "playlist_id": playlist_id,
                                                "video_id": video_id
                                            },
                                            type: 'post',
                                            success: function (response) {
                                                reloadPlayLists();
                                                $(".playListsIds" + video_id).prop("checked", false);
                                                $(currentObject).closest('.galleryVideo').fadeOut();
                                                modal.hidePleaseWait();
                                            }
                                        });
                                    }
                                });

                    });

                    $('.deletePlaylist').click(function () {
                        currentObject = this;

                        swal({
                            title: "<?php echo __("Are you sure?"); ?>",
                            text: "<?php echo __("You will not be able to recover this action!"); ?>",
                            icon: "warning",
                            buttons: true,
                            dangerMode: true,
                        })
                                .then(function (willDelete) {
                                    if (willDelete) {

                                        modal.showPleaseWait();
                                        var playlist_id = $(currentObject).attr('playlist_id');
                                        console.log(playlist_id);
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistRemove.php',
                                            data: {
                                                "playlist_id": playlist_id
                                            },
                                            type: 'post',
                                            success: function (response) {
                                                $(currentObject).closest('.panel').slideUp();
                                                modal.hidePleaseWait();
                                            }
                                        });
                                    }
                                });

                    });

                    $('.statusPlaylist').click(function () {
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
                            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistStatus.php',
                            data: {
                                "playlist_id": playlist_id,
                                "status": status
                            },
                            type: 'post',
                            success: function (response) {

                                modal.hidePleaseWait();
                            }
                        });

                    });

                    $('.renamePlaylist').click(function () {
                        currentObject = this;
                        swal({
                            text: "<?php echo __("Change Playlist Name"); ?>!",
                            content: "input",
                            button: {
                                text: "<?php echo __("Confirm Playlist name"); ?>",
                                closeModal: false,
                            },
                        }).then(function (name) {
                            if (!name)
                                throw null;
                            modal.showPleaseWait();
                            var playlist_id = $(currentObject).attr('playlist_id');
                            console.log(playlist_id);
                            return fetch('<?php echo $global['webSiteRootURL']; ?>objects/playlistRename.php?playlist_id=' + playlist_id + '&name=' + encodeURI(name));
                        }).then(function (results) {
                            return results.json();
                        }).then(function (response) {
                            if (response.error) {
                                avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                modal.hidePleaseWait();
                            } else {
                                $(currentObject).closest('.panel').find('.playlistName').text(response.name);
                                swal.stopLoading();
                                swal.close();
                                modal.hidePleaseWait();
                            }
                        }).catch(function (err) {
                            if (err) {
                                swal("Oh noes!", "The AJAX request failed!", "error");
                            } else {
                                swal.stopLoading();
                                swal.close();
                            }
                            modal.hidePleaseWait();
                        });
                        ;

                    });

                    $('.sortNow').click(function () {
                        var $val = $(this).siblings("input").val();
                        sortNow(this, $val);
                    });

                    $('.video_order').keypress(function (e) {
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
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

        <script>
            var currentSerieVideos_id = 0;
            var videoWasAdded = false;

            function openVideoSearch(videos_id) {
                currentSerieVideos_id = videos_id;
                $('#videoSearchModal').modal();
            }

            $(document).ready(function () {

                $('#videoSearch-form').submit(function (event) {
                    event.preventDefault();
                    videoSearch(0);
                });

                $('#serieSearch-form').submit(function (event) {
                    event.preventDefault();
                    videoSearch(1);
                });

                $('#videoSearchModal').on('hidden.bs.modal', function () {
                    if (videoWasAdded) {
                        modal.showPleaseWait();
                        location.reload();
                    }
                });

            });

            function videoSearch(is_serie) {
                modal.showPleaseWait();
                var searchPhrase = $('#videoSearch-input').val();
                if (is_serie) {
                    searchPhrase = $('#serieSearch-input').val();
                }
                $.ajax({
                    url: webSiteRootURL + 'plugin/API/get.json.php?APIName=video&rowCount=10&is_serie=' + is_serie + '&searchPhrase=' + searchPhrase,
                    success: function (response) {
                        console.log(response);
                        var resultId = '#searchVideoResult';
                        if (is_serie) {
                            resultId = '#searchSerieResult';
                        }
                        $(resultId).empty();
                        var rows = response.response.rows;
                        for (var i in rows) {
                            if (typeof rows[i] !== 'object') {
                                continue;
                            }
                            if (rows[i].id == currentSerieVideos_id) {
                                continue;
                            }
                            var html = '<button type="button" class="btn btn-default btn-block"  data-toggle="tooltip" title="<?php echo __('Add To Serie'); ?>" onclick="addToSerie(<?php echo $program['id']; ?>, ' + rows[i].id + ');" id="videos_id_' + rows[i].id + '_playlists_id_<?php echo $program['id']; ?>" ><i class="fas fa-plus"></i> ' + rows[i].title + '</button>';
                            $(resultId).append(html);
                        }
                        modal.hidePleaseWait();
                    }
                });
            }

            function addToSerie(playlists_id, videos_id) {
                addVideoToPlayList(videos_id, true, playlists_id);
                $('#videos_id_' + videos_id + '_playlists_id_' + playlists_id).fadeOut();
                videoWasAdded = true;
            }

        </script>
    </body>
</html>