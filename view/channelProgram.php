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
    $videos_id = $videosArrayId[0];
}
$playListsObj = AVideoPlugin::getObjectData("PlayLists");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Program"); ?></title>
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
                } else if (empty($videosArrayId)) {
                    $videosP = array();
                } else if ($advancedCustom->AsyncJobs) {
                    $videosP = Video::getAllVideosAsync("viewable", false, true, $videosArrayId, false, true);
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
                $playListButtons = AVideoPlugin::getPlayListButtons($program['id']);
                @$timesC[__LINE__] += microtime(true) - $startC;
                $startC = microtime(true);
                ?>

                <div class="panel panel-default program" playListId="<?php echo $program['id']; ?>">
                    <div class="panel-heading">

                        <strong style="font-size: 1.1em;" class="playlistName"><?php echo $program['name']; ?> </strong>

                        <?php
                        if (!empty($videosArrayId)) {
                            $link = PlayLists::getLink($program['id']);
                            ?>
                            <a href="<?php echo $link; ?>" class="btn btn-xs btn-default playAll hrefLink" ><span class="fa fa-play"></span> <?php echo __("Play All"); ?></a><?php echo $playListButtons; ?>
                            <?php
                            echo PlayLists::getPlayLiveButton($program['id']);
                        }
                        if ($isMyChannel) {
                            ?>
                            <script>
                                $(function () {
                                    $("#sortable<?php echo $program['id']; ?>").sortable({
                                        stop: function (event, ui) {
                                            modal.showPleaseWait();
                                            saveSortable(this, <?php echo $program['id']; ?>);
                                        }
                                    });
                                    $("#sortable<?php echo $program['id']; ?>").disableSelection();
                                });
                            </script>
                            <div class="dropdown" style="display: inline-block;">
                                <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown"><?php echo __("Auto Sort"); ?>
                                    <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=1"><?php echo __("Alphabetical"); ?> A-Z</a></li>
                                    <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=2"><?php echo __("Alphabetical"); ?> Desc Z-A</a></li>
                                    <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=3"><?php echo __("Created Date"); ?> 0-9</a></li>
                                    <li><a href="<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php?playlist_id=<?php echo $program['id']; ?>&sort=4"><?php echo __("Created Date"); ?> Desc 9-0</a></li>
                                </ul>
                            </div>
                            <div class="pull-right btn-group">
                                <?php
                                if ($program['status'] != "favorite" && $program['status'] != "watch_later") {
                                    if (AVideoPlugin::isEnabledByName("PlayLists")) {
                                        ?>
                                        <button class="btn btn-xs btn-default" onclick="copyToClipboard($('#playListEmbedCode<?php echo $program['id']; ?>').val());setTextEmbedCopied();" ><span class="fa fa-copy"></span> <span id="btnEmbedText"><?php echo __("Copy embed code"); ?></span></button>
                                        <input type="hidden" id="playListEmbedCode<?php echo $program['id']; ?>" value='<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/embed.php?playlists_id=<?php echo $program['id']; ?>" frameborder="0" allowfullscreen="allowfullscreen" allow="autoplay"></iframe>'/>
                                        <?php
                                    }
                                    ?>
                                    <button class="btn btn-xs btn-info seriePlaylist" playlist_id="<?php echo $program['id']; ?>" ><i class="fas fa-film"></i> <?php echo __("Serie"); ?></button>

                                    <div id="seriePlaylistModal" class="modal fade" tabindex="-1" role="dialog" >
                                        <div class="modal-dialog" role="document" style="width: 90%; margin: auto;">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?php echo __("Serie"); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <iframe style="width: 100%; height: 80vh;" src="about:blank">

                                                    </iframe>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->
                                    <script>
                                        $(function () {
                                            $('.seriePlaylist').click(function () {
                                                $($('#seriePlaylistModal').find('iframe')[0]).attr('src', 'about:blank');
                                                var playlist_id = $(this).attr('playlist_id');
                                                $($('#seriePlaylistModal').find('iframe')[0]).attr('src', '<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/playListToSerie.php?playlist_id=' + playlist_id);
                                                $('#seriePlaylistModal').modal();
                                                //$('#seriePlaylistModal').modal('hide');
                                            });
                                        });
                                    </script>

                                    <button class="btn btn-xs btn-danger deletePlaylist" playlist_id="<?php echo $program['id']; ?>" ><i class="fas fa-trash"></i> <?php echo __("Delete"); ?></button>
                                    <button class="btn btn-xs btn-primary renamePlaylist" playlist_id="<?php echo $program['id']; ?>" ><i class="fas fa-edit"></i> <?php echo __("Rename"); ?></button>
                                    <button class="btn btn-xs btn-default statusPlaylist statusPlaylist<?php echo $program['id']; ?>" playlist_id="<?php echo $program['id']; ?>" style="" >
                                        <span class="fa fa-lock" id="statusPrivate<?php echo $program['id']; ?>" style="color: red; <?php
                                        if ($program['status'] !== 'private') {
                                            echo ' display: none;';
                                        }
                                        ?> " ></span> 
                                        <span class="fa fa-globe" id="statusPublic<?php echo $program['id']; ?>" style="color: green; <?php
                                        if ($program['status'] !== 'public') {
                                            echo ' display: none;';
                                        }
                                        ?>"></span> 
                                        <span class="fa fa-eye-slash" id="statusUnlisted<?php echo $program['id']; ?>" style="color: gray;   <?php
                                        if ($program['status'] !== 'unlisted') {
                                            echo ' display: none;';
                                        }
                                        ?>"></span>
                                    </button>
                                    <?php
                                }
                                ?>
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
                                foreach ($videosP as $value) {
                                    if (empty($value['created'])) {
                                        $count++;
                                        continue;
                                    }
                                    $episodeLink = "{$global['webSiteRootURL']}program/{$program['id']}/{$count}";
                                    $count++;
                                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                    $name = User::getNameIdentificationById($value['users_id']);

                                    $images = Video::getImageFromFilename($value['filename'], $value['type'], true);
                                    $imgGif = $images->thumbsGif;
                                    $poster = $images->thumbsJpg;
                                    $class = "";
                                    $style = "";
                                    if ($count > 6) {
                                        $class = "showMoreLess{$program['id']}";
                                        $style = "display: none;";
                                    }
                                    ?>
                                    <li class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo showMoreLess <?php echo $class; ?> " id="<?php echo $value['id']; ?>" style="padding: 1px;  <?php echo $style; ?>">
                                        <div class="panel panel-default" playListId="<?php echo $program['id']; ?>" style="min-height: 215px;">
                                            <div class="panel-body" style="overflow: hidden;">
                                                <a class="aspectRatio16_9" href="<?php echo $episodeLink; ?>" title="<?php echo $value['title']; ?>" style="margin: 15px 0; overflow: visible;" >
                                                    <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" />
                                                    <?php
                                                    if ($value['type'] !== 'pdf' && $value['type'] !== 'article' && $value['type'] !== 'serie') {
                                                        ?>
                                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                                        <div class="progress" style="height: 3px; margin-bottom: 2px;">
                                                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div> 
                                                        <?php
                                                    }
                                                    ?>
                                                </a>
                                                <a class="hrefLink" href="<?php echo $episodeLink; ?>" title="<?php echo $value['title']; ?>">
                                                    <h2><?php echo $value['title']; ?></h2>
                                                </a>
                                                <div class="text-muted galeryDetails" style="min-height: 60px;">
                                                    <div>
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
                                                        <?php
                                                    }
                                                    ?>

                                                    <div>
                                                        <i class="far fa-clock"></i>
                                                        <?php
                                                        echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                        ?>
                                                    </div>
                                                    <div>
                                                        <i class="fa fa-user"></i>
                                                        <?php
                                                        echo $name;
                                                        ?>
                                                    </div>
                                                    <?php
                                                    if (Video::canEdit($value['id'])) {
                                                        ?>
                                                        <div>
                                                            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $value['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>


                                                        </div>
                                                        <?php
                                                    }
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
                                                            <span class="text-primary" playlist_id="<?php echo $program['id']; ?>" video_id="<?php echo $value['id']; ?>">
                                                                <i class="fas fa-sort-numeric-down"></i> <?php echo __("Sort"); ?> 
                                                                <input type="number" step="1" class="video_order" value="<?php echo intval($program['videos'][$count - 1]['video_order']); ?>" style="max-width: 50px;">
                                                                <button class="btn btn-sm btn-xs sortNow"><i class="fas fa-check-square"></i></button>
                                                            </span>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
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
                                <?php
                            }
                            ?>
                        </div>  
                        <?php
                    }
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
            $(document).ready(function () {


            });

        </script>
    </body>
</html>