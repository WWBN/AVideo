<?php
global $global, $config, $isChannel;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';

if (empty($_GET['channelName'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
} else {
    $user = User::getChannelOwner($_GET['channelName']);
    if (!empty($user)) {
        $_GET['user_id'] = $user['id'];
    } else {
        $_GET['user_id'] = $_GET['channelName'];
    }
}
$user_id = $_GET['user_id'];

$timeLog2 = __FILE__ . " - channelPlayList: {$_GET['channelName']}";
TimeLogStart($timeLog2);

$publicOnly = true;
$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $publicOnly = false;
    $isMyChannel = true;
}
if (empty($_GET['current'])) {
    $_POST['current'] = 1;
} else {
    $_POST['current'] = intval($_GET['current']);
}
$_REQUEST['rowCount'] = 4;
$playlists = PlayList::getAllFromUser($user_id, $publicOnly);
$current = $_POST['current'];
unset($_POST['current']);
?>
<div class="programsContainerItem">
    <?php
    if (empty($playlists)) {
        die("</div>");
    }
    $playListsObj = AVideoPlugin::getObjectData("PlayLists");
    TimeLogEnd($timeLog2, __LINE__);
    $channelName = @$_GET['channelName'];
    unset($_GET['channelName']);
    $startC = microtime(true);
    TimeLogEnd($timeLog2, __LINE__);

    $countSuccess = 0;
    $get = array();
    if (!empty($_GET['channelName'])) {
        $get = array('channelName' => $_GET['channelName']);
    }
    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
    foreach ($playlists as $key => $playlist) {
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        $_REQUEST['current'] = 1;
        $_REQUEST['rowCount'] = 6;
        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlist['id']);
        $_REQUEST['current'] = 1;
        $_REQUEST['rowCount'] = 6;
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        $rowCount = $_POST['rowCount'];
        $_REQUEST['rowCount'] = 6;

        //getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
        if (empty($videosArrayId) && ($playlist['status'] == "favorite" || $playlist['status'] == "watch_later")) {
            unset($playlists[$key]);
            continue;
        } else if (empty($videosArrayId)) {
            $videosP = array();
        } else if ($advancedCustom->AsyncJobs) {
            $videosP = Video::getAllVideosAsync("viewable", false, true, $videosArrayId, false, true);
        } else {
            $videosP = Video::getAllVideos("viewable", false, true, $videosArrayId, false, true);
        }//var_dump($videosArrayId, $videosP); exit;

        $totalDuration = 0;
        foreach ($videosP as $value) {
            $totalDuration += durationToSeconds($value['duration']);
        }

        $_REQUEST['rowCount'] = $rowCount;
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        //_error_log("channelPlaylist videosP: ".json_encode($videosP));
        $videosP = PlayList::sortVideos($videosP, $videosArrayId);
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        //_error_log("channelPlaylist videosP2: ".json_encode($videosP));
        //_error_log("channelPlaylist videosArrayId: ".json_encode($videosArrayId));
        $playListButtons = AVideoPlugin::getPlayListButtons($playlist['id']);
        @$timesC[__LINE__] += microtime(true) - $startC;
        $startC = microtime(true);
        $countSuccess++;
        ?>

        <div class="panel panel-default" playListId="<?php echo $playlist['id']; ?>">
            <div class="panel-heading">

                <strong style="font-size: 1.1em;" class="playlistName">
                    <?php echo __($playlist['name']); ?> (<?php echo secondsToDuration($totalDuration); ?>)
                </strong>

                <?php
                if (!empty($videosArrayId)) {
                    $link = PlayLists::getLink($playlist['id']);
                    ?>
                    <a href="<?php echo $link; ?>" class="btn btn-xs btn-default playAll hrefLink" ><span class="fa fa-play"></span> <?php echo __("Play All"); ?></a><?php echo $playListButtons; ?>
                    <?php
                }
                echo PlayLists::getPlayLiveButton($playlist['id']);
                ?>
                <div class="pull-right btn-group" style="display: inline-flex;">
                    <?php
                    if ($isMyChannel) {
                        echo PlayLists::getShowOnTVSwitch($playlist['id']);
                        if ($playlist['status'] != "favorite" && $playlist['status'] != "watch_later") {
                            if (AVideoPlugin::isEnabledByName("PlayLists")) {
                                ?>
                                <button class="btn btn-xs btn-default" onclick="copyToClipboard($('#playListEmbedCode<?php echo $playlist['id']; ?>').val()); setTextEmbedCopied();" ><span class="fa fa-copy"></span> <span id="btnEmbedText"><?php echo __("Copy embed code"); ?></span></button>
                                <input type="hidden" id="playListEmbedCode<?php echo $playlist['id']; ?>" value='<?php
                                $code = str_replace("{embedURL}", "{$global['webSiteRootURL']}plugin/PlayLists/embed.php?playlists_id={$playlist['id']}", $advancedCustom->embedCodeTemplate);
                                echo ($code);
                                ?>'/>
                                       <?php
                                   }
                                   ?>
                            <button class="btn btn-xs btn-info seriePlaylist" playlist_id="<?php echo $playlist['id']; ?>" ><i class="fas fa-film"></i> <?php echo __("Serie"); ?></button>

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

                            <button class="btn btn-xs btn-danger deletePlaylist" playlist_id="<?php echo $playlist['id']; ?>" ><i class="fas fa-trash"></i> <?php echo __("Delete"); ?></button>
                            <button class="btn btn-xs btn-primary renamePlaylist" playlist_id="<?php echo $playlist['id']; ?>" ><i class="fas fa-edit"></i> <?php echo __("Rename"); ?></button>
                            <button class="btn btn-xs btn-default statusPlaylist statusPlaylist<?php echo $playlist['id']; ?>" playlist_id="<?php echo $playlist['id']; ?>" style="" >
                                <span class="fa fa-lock" id="statusPrivate<?php echo $playlist['id']; ?>" style="color: red; <?php
                                if ($playlist['status'] !== 'private') {
                                    echo ' display: none;';
                                }
                                ?> " ></span> 
                                <span class="fa fa-globe" id="statusPublic<?php echo $playlist['id']; ?>" style="color: green; <?php
                                if ($playlist['status'] !== 'public') {
                                    echo ' display: none;';
                                }
                                ?>"></span> 
                                <span class="fa fa-eye-slash" id="statusUnlisted<?php echo $playlist['id']; ?>" style="color: gray;   <?php
                                if ($playlist['status'] !== 'unlisted') {
                                    echo ' display: none;';
                                }
                                ?>"></span>
                            </button>
                            <?php
                        }
                    }
                    ?>
                    <a class="btn btn-xs btn-default" href="<?php echo $global['webSiteRootURL']; ?>viewProgram/<?php echo $playlist['id']; ?>/<?php echo urlencode($playlist['name']); ?>/">
                        <?php echo __('More'); ?> <i class="fas fa-ellipsis-h"></i> 
                    </a>
                </div>
            </div>

            <?php
            if (!empty($videosArrayId)) {
                ?>

                <div class="panel-body">
                    <?php
                    $serie = PlayLists::isPlayListASerie($playlist['id']);
                    if (!empty($serie)) {
                        $images = Video::getImageFromFilename($serie['filename'], $serie['type'], true);
                        $imgGif = $images->thumbsGif;
                        $poster = $images->thumbsJpg;
                        $category = new Category($serie['categories_id']);
                        ?>
                        <div style="overflow: hidden;">
                            <div style="display: flex; margin-bottom: 10px;">
                                <div style="margin-right: 5px; min-width: 30%;" >
                                    <img src="<?php echo $poster; ?>" alt="<?php echo $serie['title']; ?>" class="img img-responsive" style="max-height: 200px;" />
                                </div>  
                                <div>
                                    <a class="hrefLink" href="<?php echo Video::getLink($serie['id'], $serie['clean_title']); ?>" title="<?php echo $serie['title']; ?>">
                                        <h2><?php echo $serie['title']; ?></h2>
                                    </a>
                                    <small class="text-muted galeryDetails">
                                        <a class="label label-default" href="<?php echo Video::getLink($serie['id'], $category->getClean_name(), false, $get); ?>">
                                            <?php
                                            if (!empty($category->getIconClass())) {
                                                ?>
                                                <i class="<?php echo $category->getIconClass(); ?>"></i>
                                                <?php
                                            }
                                            ?>
                                            <?php echo $category->getName(); ?>
                                        </a>
                                        <?php
                                        $serie['tags'] = Video::getTags($serie['id']);
                                        foreach ($serie['tags'] as $value2) {
                                            if ($value2->label === __("Group")) {
                                                ?>
                                                <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                <?php
                                            }
                                        }
                                        ?>
                                        <i class="far fa-clock"></i>
                                        <?php
                                        echo humanTiming(strtotime($serie['created'])), " ", __('ago');
                                        ?>

                                        <?php
                                        if (!empty($serie['trailer1'])) {
                                            ?>
                                            <a href="#" class="btn btn-xs btn-warning" onclick="$(this).removeAttr('href'); $('#serie<?php echo $serie['id']; ?> img').fadeOut(); $('<iframe>', {
                                                                        src: '<?php echo parseVideos($serie['trailer1'], 1, 0, 0, 0, 1, 0, 'fill'); ?>',
                                                                        id: 'myFrame<?php echo $serie['id']; ?>',
                                                                        allow: 'autoplay',
                                                                        frameborder: 0,
                                                                        height: 200,
                                                                        width: '100%',
                                                                        scrolling: 'no'
                                                                    }).appendTo('#serie<?php echo $serie['id']; ?>');
                                                                    $(this).removeAttr('onclick');
                                                                    $(this).fadeOut();
                                                                    return false;">
                                                <span class="fa fa-film"></span> 
                                                <span class="hidden-xs"><?php echo __("Trailer"); ?></span>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                    </small>
                                    <p>
                                        <?php echo $serie['description']; ?>
                                    </p>
                                </div>  
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="clearfix"></div>
                    <?php
                    $count = 0;
                    $_REQUEST['site'] = get_domain($global['webSiteRootURL']);
                    foreach ($videosP as $value) {
                        // make sure the video exists
                        if (empty($value['created'])) {
                            $count++;
                            continue;
                        }
                        $episodeLink = "{$global['webSiteRootURL']}program/{$playlist['id']}/{$count}/{$channelName}/" . urlencode($playlist['name']) . "/{$value['clean_title']}";
                        $count++;
                        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                        $name = User::getNameIdentificationById($value['users_id']);

                        $images = Video::getImageFromFilename($value['filename'], $value['type'], true);
                        $imgGif = $images->thumbsGif;
                        $poster = $images->thumbsJpg;
                        $class = "";
                        ?>
                        <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo <?php echo $class; ?> " id="<?php echo $value['id']; ?>" style="padding: 1px;">
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
                                if (User::isLogged() && !empty($program)) {
                                    ?>
                                    <div class="galleryVideoButtons">
                                        <?php
                                        //var_dump($value['isWatchLater'], $value['isFavorite']);
                                        if ($value['isWatchLater']) {
                                            $watchLaterBtnAddedStyle = "";
                                            $watchLaterBtnStyle = "display: none;";
                                        } else {
                                            $watchLaterBtnAddedStyle = "display: none;";
                                            $watchLaterBtnStyle = "";
                                        }
                                        if ($value['isFavorite']) {
                                            $favoriteBtnAddedStyle = "";
                                            $favoriteBtnStyle = "display: none;";
                                        } else {
                                            $favoriteBtnAddedStyle = "display: none;";
                                            $favoriteBtnStyle = "";
                                        }
                                        ?>

                                        <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, false, <?php echo $value['watchLaterId']; ?>);
                                                                return false;" class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded<?php echo $value['id']; ?>" title="<?php echo __("Added On Watch Later"); ?>" style="color: #4285f4;<?php echo $watchLaterBtnAddedStyle; ?>" ><i class="fas fa-check"></i></button> 
                                        <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, true, <?php echo $value['watchLaterId']; ?>);
                                                                return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn<?php echo $value['id']; ?>" title="<?php echo __("Watch Later"); ?>" style="<?php echo $watchLaterBtnStyle; ?>" ><i class="fas fa-clock"></i></button>
                                        <br>
                                        <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, false, <?php echo $value['favoriteId']; ?>);
                                                                return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded<?php echo $value['id']; ?>" title="<?php echo __("Added On Favorite"); ?>" style="color: #4285f4; <?php echo $favoriteBtnAddedStyle; ?>"><i class="fas fa-check"></i></button>  
                                        <button onclick="addVideoToPlayList(<?php echo $value['id']; ?>, true, <?php echo $value['favoriteId']; ?>);
                                                                return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn<?php echo $value['id']; ?>" title="<?php echo __("Favorite"); ?>" style="<?php echo $favoriteBtnStyle; ?>" ><i class="fas fa-heart" ></i></button>    

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
                                        <span style=" cursor: pointer;" class="btn-link text-primary removeVideo" playlist_id="<?php echo $playlist['id']; ?>" video_id="<?php echo $value['id']; ?>">
                                            <i class="fa fa-trash"></i> <?php echo __("Remove"); ?>
                                        </span>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <?php
            }
            if(PlayLists::showTVFeatures()){
            ?>
            <div class="panel-footer">
                <?php 
                $_REQUEST['user_id'] = $user_id;
                $_REQUEST['playlists_id'] = $playlist['id'];
                include $global['systemRootPath'] . 'plugin/PlayLists/epg.html.php';
                ?>
            </div>
            <?php
            }
            ?>
        </div>
    <?php
}
if (!empty($videosP) && empty($countSuccess)) {
    header("Location: {$global['webSiteRootURL']}view/channelPlaylistItems.php?current=" . (count($playlists) ? $_POST['current'] + 1 : $_POST['current']) . "&channelName={$_GET['channelName']}");
    exit;
}
TimeLogEnd($timeLog2, __LINE__);
$_GET['channelName'] = $channelName;
?>
    <script>

        $(function () {
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
$_POST['current'] = $current;
?>
    -->
</div>
<p class="pagination">
    <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>view/channelPlaylistItems.php?current=<?php echo count($playlists) ? $_POST['current'] + 1 : $_POST['current']; ?>&channelName=<?php echo $_GET['channelName']; ?>"></a>
</p>