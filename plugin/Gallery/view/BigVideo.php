<?php
if(empty($crc)){
    $crc = uniqid();
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    $name = User::getNameIdentificationById($video['users_id']);
    if(empty($get)){
        $get = array();
    }
    ?>
    <div class="clear clearfix">
        <div class="row thumbsImage">
            <div class="col-sm-6">
                <a class="galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo Video::getLink($video['id'], $video['clean_title'], false, $get); ?>" title="<?php echo $video['title']; ?>" style="">
                    <?php
                    $images = Video::getImageFromFilename($video['filename'], $video['type']);
                    $imgGif = $images->thumbsGif;
                    $poster = $images->poster;
                    ?>
                    <div class="aspectRatio16_9">
                        <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="thumbsJPG img img-responsive <?php echo ($poster != $images->thumbsJpgSmall) ? "blur" : ""; ?>" style="height: auto; width: 100%;" id="thumbsJPG<?php echo $video['id']; ?>" />
                        <?php if (!empty($imgGif)) { ?>
                            <img src="<?php echo $global['webSiteRootURL']; ?>view/img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $video['title']; ?>" id="thumbsGIF<?php echo $video['id']; ?>" class="thumbsGIF img-responsive <?php echo @$img_portrait; ?>  rotate<?php echo $video['rotation']; ?>" height="130" />
                        <?php } ?>
                    </div>
                    <span class="duration"><?php echo Video::getCleanDuration($video['duration']); ?></span>
                    <div class="progress" style="height: 3px;">
                        <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $video['progress']['percent'] ?>%;" aria-valuenow="<?php echo $video['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6">
                <a class="h6 galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo Video::getLink($video['id'], $video['clean_title'], false, $get); ?>" title="<?php echo $video['title']; ?>">
                    <h1><?php echo $video['title']; ?></h1>
                </a>
                <div class="mainAreaDescriptionContainer">
                    <h4 class="mainAreaDescription" itemprop="description"><?php echo nl2br(textToLink($video['description'])); ?></h4>
                </div>
                <div class="text-muted galeryDetails">
                    <div>
                            <?php if (empty($_GET['catName'])) { ?>
                            <a class="label label-default" href="<?php echo Video::getLink($video['id'], $video['clean_title'], false, $get); ?>/">
                                <?php
                                if (!empty($video['iconClass'])) {
                                    ?>
                                    <i class="<?php echo $video['iconClass']; ?>"></i>
                                    <?php
                                }
                                ?>
                                <?php echo $video['category']; ?>
                            </a>
                        <?php } ?>
                        <?php
                        if (!empty($obj->showTags)) {
                            $video['tags'] = Video::getTags($video['id']);
                            if (!empty($video['tags'])) {
                                foreach ($video['tags'] as $value2) {
                                    if (!empty($value2->label) && $value2->label === __("Group")) {
                                        ?><span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
                                    }
                                }
                            }
                        }
                        ?>
                    </div>
                    <div>
                        <i class="fa fa-eye"></i>
                        <span itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                    </div>
                    <div>
                        <i class="fa fa-clock-o"></i>
                        <?php echo humanTiming(strtotime($video['videoCreation'])), " ", __('ago'); ?>
                    </div>
                    <div>
                        <i class="fa fa-user"></i>
                        <a class="text-muted" href="<?php echo User::getChannelLink($video['users_id']); ?>">
                            <?php echo $name; ?>
                        </a>
                    </div>
                    <?php if (Video::canEdit($video['id'])) { ?>
                        <div>
                            <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $video['id']; ?>" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                        </div>
                    <?php } ?>


                    <div class="">
                        <?php if ((empty($_POST['disableAddTo'])) && (( ($advancedCustom != false) && ($advancedCustom->disableShareAndPlaylist == false)) || ($advancedCustom == false))) { ?>
                            <a href="#" class="text-primary" style="float:right;" id="addBtn<?php echo $video['id'] . $crc; ?>" data-placement="bottom">
                                <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                            </a>
                            <div class="webui-popover-content" >
                                <?php if (User::isLogged()) { ?>
                                    <form role="form">
                                        <div class="form-group">
                                            <input class="form-control" id="searchinput<?php echo $video['id'] . $crc; ?>" type="search" placeholder="<?php echo __("Search"); ?>..." />
                                        </div>
                                        <div id="searchlist<?php echo $video['id'] . $crc; ?>" class="list-group">
                                        </div>
                                    </form>
                                    <div>
                                        <hr>
                                        <div class="form-group">
                                            <input id="playListName<?php echo $video['id'] . $crc; ?>" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                                        </div>
                                        <div class="form-group">
                                            <?php echo __("Make it public"); ?>
                                            <div class="material-switch pull-right">
                                                <input id="publicPlayList<?php echo $video['id'] . $crc; ?>" name="publicPlayList" type="checkbox" checked="checked"/>
                                                <label for="publicPlayList" class="label-success"></label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button class="btn btn-success btn-block" id="addPlayList<?php echo $video['id'] . $crc; ?>" ><?php echo __("Create a New Play List"); ?></button>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <h5><?php echo __("Want to watch this again later?"); ?></h5>
                                    <?php echo __("Sign in to add this video to a playlist."); ?>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                                        <span class="fas fa-sign-in-alt"></span>
                                        <?php echo __("Login"); ?>
                                    </a>
                                <?php } ?>
                            </div>
                            <script>
                                var tmpPIdBigVideo;
                                var tmpSaveBigVideo;
                                function loadPlayLists<?php echo $video['id'] . $crc; ?>() {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlists.json.php',
                                        success: function (response) {
                                            $('#searchlist<?php echo $video['id'] . $crc; ?>').html('');
                                            for (var i in response) {
                                                if (!response[i].id) {
                                                    continue;
                                                }
                                                var icon = "lock"
                                                if (response[i].status == "public") {
                                                    icon = "globe"
                                                }
                                                var checked = "";
                                                for (var x in response[i].videos) {
                                                    if (typeof (response[i].videos[x]) === 'object' && response[i].videos[x].videos_id ==<?php echo $video['id']; ?>) {
                                                        checked = "checked";
                                                    }
                                                }
                                                $("#searchlist<?php echo $video['id'] . $crc; ?>").append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>'
                                                        + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault'
                                                        + response[i].id + '<?php echo $video['id'] . $crc; ?>" name="someSwitchOption' + response[i].id + '<?php echo $video['id'] . $crc; ?>" class="playListsIds<?php echo $video['id'] . $crc; ?> playListsIds' + response[i].id + ' " type="checkbox" value="'
                                                        + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault'
                                                        + response[i].id + '<?php echo $video['id'] . $crc; ?>" class="label-success"></label></div></a>');

                                            }
                                            $('#searchlist<?php echo $video['id'] . $crc; ?>').btsListFilter('#searchinput<?php echo $video['id'] . $crc; ?>', {itemChild: 'span'});
                                            $('.playListsIds<?php echo $video['id'] . $crc; ?>').change(function () {
                                                modal.showPleaseWait();

                                                //tmp-variables simply make the values avaible on success.
                                                tmpPIdBigVideo = $(this).val();
                                                tmpSaveBigVideo = $(this).is(":checked");
                                                $.ajax({
                                                    url: '<?php echo $global['webSiteRootURL']; ?>objects/playListAddVideo.json.php',
                                                    method: 'POST',
                                                    data: {
                                                        'videos_id': <?php echo $video['id']; ?>,
                                                        'add': $(this).is(":checked"),
                                                        'playlists_id': $(this).val()
                                                    },
                                                    success: function (response) {
        <?php
        global $isChannel;
        if (!empty($isChannel)) {
            ?>
                                                            refreshPlayLists('playlistContainer');
        <?php } ?>

                                                        $(".playListsIds" + tmpPIdBigVideo).prop("checked", tmpSaveBigVideo);
                                                        modal.hidePleaseWait();
                                                    }
                                                });
                                                return false;
                                            });
                                        }
                                    });
                                }
                                $(document).ready(function () {
                                    loadPlayLists<?php echo $video['id'] . $crc; ?>();
                                    $('#addBtn<?php echo $video['id'] . $crc; ?>').webuiPopover();
                                    $('#addPlayList<?php echo $video['id'] . $crc; ?>').click(function () {
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistAddNew.json.php',
                                            method: 'POST',
                                            data: {
                                                'videos_id': <?php echo $video['id']; ?>,
                                                'status': $('#publicPlayList<?php echo $video['id'] . $crc; ?>').is(":checked") ? "public" : "private",
                                                'name': $('#playListName<?php echo $video['id'] . $crc; ?>').val()
                                            },
                                            success: function (response) {
                                                if (response.status * 1 > 0) {
                                                    // update list
        <?php
        global $isChannel;
        if (!empty($isChannel)) {
            ?>
                                                        refreshPlayLists('playlistContainer');
        <?php } ?>
                                                    loadPlayLists<?php echo $video['id'] . $crc; ?>();
                                                    $('#searchlist<?php echo $video['id'] . $crc; ?>').btsListFilter('#searchinput<?php echo $video['id'] . $name; ?>', {itemChild: 'span'});
                                                    $('#playListName<?php echo $video['id'] . $crc; ?>').val("");
                                                    $('#publicPlayList<?php echo $video['id'] . $crc; ?>').prop('checked', true);
                                                }
                                                modal.hidePleaseWait();
                                            }
                                        });
                                        return false;
                                    });
                                });
                            </script>
                        <?php } ?>
                    </div>

                    <?php
                    if (CustomizeUser::canDownloadVideosFromUser($video['users_id'])) {
                        ?>
                        <div style="position: relative; overflow: visible;">
                            <button type="button" class="btn btn-default btn-sm btn-xs"  data-toggle="dropdown">
                                <i class="fa fa-download"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <?php
                                $files = getVideosURL($video['filename']);
                                //var_dump($files);exit;
                                foreach ($files as $key => $theLink) {
                                    if ($theLink['type'] !== 'video' && $theLink['type'] !== 'audio') {
                                        continue;
                                    }
                                    $path_parts = pathinfo($theLink['filename']);
                                    ?>
                                    <li>
                                        <a href="<?php echo $theLink['url']; ?>?download=1&title=<?php echo urlencode($video['title'] . "_{$key}_.{$path_parts['extension']}"); ?>">
                                            <?php echo __("Download"); ?> <?php echo $key; ?>
                                        </a>
                                    </li>
                                <?php }
                                ?>
                            </ul>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
} else if (!empty($_GET['showOnly'])) {
    ?>
    <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
    <?php
}
