<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
if (empty($video) && !empty($_GET['videos_id'])) {
    $video = Video::getVideo(intval($_GET['videos_id']), "viewable", false, false, true, true);
    $name = User::getNameIdentificationById($video['users_id']);
    $name = "<a href='" . User::getChannelLink($video['users_id']) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($video['users_id']) . "</a>";
    $subscribe = Subscribe::getButton($video['users_id']);
    $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="User Photo" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br />' . $subscribe . '<br /><small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
    $source = Video::getSourceFile($video['filename']);
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else if ($video['type'] == "audio") {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    } else if ($video['type'] === 'zip') {
        $type = 'zip';
    } else if ($video['type'] === 'article') {
        $type = 'article';
    }
    $images = Video::getImageFromFilename($video['filename'], $type);
    $poster = $images->poster;
    if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
        $img = $images->posterPortrait;
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    }
}
?>


<div class="row bgWhite list-group-item">
    <div class="row divMainVideo">
        <div class="col-xs-4 col-sm-4 col-md-4">
            <img src="<?php echo $img; ?>" alt="<?php echo str_replace('"', '', $video['title']); ?>" class="img img-responsive <?php echo $img_portrait; ?> rotate<?php echo $video['rotation']; ?>" height="130" itemprop="thumbnail" />
            <?php
            if (isToShowDuration($video['type'])) {
                ?>
                <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($video['duration']); ?>" ><?php echo Video::getCleanDuration($video['duration']); ?></time>
                <?php
            }
            ?>
            <span itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
            <span itemprop="contentURL" content="<?php echo Video::getLink($video['id'], $video['clean_title']); ?>" />
            <span itemprop="embedURL" content="<?php echo Video::getLink($video['id'], $video['clean_title'], true); ?>" />
            <span itemprop="uploadDate" content="<?php echo $video['created']; ?>" />
            <span itemprop="description" content="<?php echo str_replace('"', '', $video['title']); ?> - <?php echo htmlentities($video['description']); ?>" />

        </div>
        <div class="col-xs-8 col-sm-8 col-md-8">
            <h1 itemprop="name">
                <?php
                echo $video['title'];
                if (!empty($video['id']) && Video::showYoutubeModeOptions() && Video::canEdit($video['id'])) {
                    ?>
                    <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $video['id']; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="<?php echo __("Edit Video"); ?>"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                <?php } ?>
                <small>
                    <?php
                    if (!empty($video['id'])) {
                        $video['tags'] = Video::getTags($video['id']);
                    } else {
                        $video['tags'] = array();
                    }
                    foreach ($video['tags'] as $value) {
                        if(is_array($value)){
                            $value = (object)$value;
                        }
                        if ($value->label === __("Group")) {
                            ?>
                            <span class="label label-<?php echo $value->type; ?>"><?php echo $value->text; ?></span>
                            <?php
                        }
                    }
                    ?>
                </small>
            </h1>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <?php echo $video['creator']; ?>
            </div>

            <?php
            if (Video::showYoutubeModeOptions() && empty($advancedCustom->doNotDisplayViews)) {
                ?> 
                <span class="watch-view-count pull-right text-muted" itemprop="interactionCount"><span class="view-count<?php echo $video['id']; ?>"><?php echo number_format($video['views_count'], 0); ?></span> <?php echo __("Views"); ?></span>
                <?php
            }
            ?>
            <?php
            if (AVideoPlugin::isEnabledByName("VideoTags")) {
                echo VideoTags::getLabels($video['id'], false);
            }
            ?>
        </div>
    </div>
    <?php
    if (Video::showYoutubeModeOptions()) {
        ?>
        <div class="row">
            <div class="col-md-12 watch8-action-buttons text-muted">
                <?php if (empty($advancedCustom->disableShareAndPlaylist)) { ?>
                    <?php if (CustomizeUser::canShareVideosFromVideo($video['id'])) { ?>
                        <a href="#" class="btn btn-default no-outline" id="shareBtn">
                            <span class="fa fa-share"></span> <?php echo __("Share"); ?>
                        </a>
                        <?php
                    }
                    $filesToDownload = array();
                    if (CustomizeUser::canDownloadVideosFromVideo($video['id'])) {
                        if($video['type']=="zip"){
                            $files = getVideosURLZIP($video['filename']);
                        }else{
                            $files = getVideosURL($video['filename']);
                        }
                        foreach ($files as $key => $theLink) {
                            if (empty($advancedCustom->showImageDownloadOption)) {
                                if ($key == "jpg" || $key == "gif" || $key == "webp" || $key == "pjpg" || $key == "m3u8") {
                                    continue;
                                }
                            }
                            if (strpos($theLink['url'], '?') === false) {
                                $theLink['url'] .= "?download=1&title=" . urlencode($video['title'] . "_{$key}_.mp4");
                            }
                            $filesToDownload[] = array('name' => $key, 'url' => $theLink['url']);
                        }
                        if (!empty($filesToDownload)) {
                            ?>
                            <a href="#" class="btn btn-default no-outline" id="downloadBtn">
                                <span class="fa fa-download"></span> <?php echo __("Download"); ?>
                            </a>
                            <?php
                        }
                    }
                    ?>
                <?php } echo AVideoPlugin::getWatchActionButton($video['id']); ?>
                <?php
                if (!empty($video['id']) && empty($advancedCustom->removeThumbsUpAndDown)) {
                    ?>
                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == - 1) ? "myVote" : "" ?>" id="dislikeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("DonÂ´t like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                        <span class="fa fa-thumbs-down"></span> <small><?php echo $video['dislikes']; ?></small>
                    </a>
                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == 1) ? "myVote" : "" ?>" id="likeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("Like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                        <span class="fa fa-thumbs-up"></span>
                        <small><?php echo $video['likes']; ?></small>
                    </a>
                    <script>
                        $(document).ready(function () {
        <?php if (User::isLogged()) { ?>
                                $("#dislikeBtn, #likeBtn").click(function () {
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>' + ($(this).attr("id") == "dislikeBtn" ? "dislike" : "like"),
                                        method: 'POST',
                                        data: {'videos_id': <?php echo $video['id']; ?>},
                                        success: function (response) {
                                            $("#likeBtn, #dislikeBtn").removeClass("myVote");
                                            if (response.myVote == 1) {
                                                $("#likeBtn").addClass("myVote");
                                            } else if (response.myVote == -1) {
                                                $("#dislikeBtn").addClass("myVote");
                                            }
                                            $("#likeBtn small").text(response.likes);
                                            $("#dislikeBtn small").text(response.dislikes);
                                        }
                                    });
                                    return false;
                                });
        <?php } else { ?>
                                $("#dislikeBtn, #likeBtn").click(function () {
                                    $(this).tooltip("show");
                                    return false;
                                });
        <?php } ?>
                        });
                    </script>

                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php if (!empty($filesToDownload) && CustomizeUser::canDownloadVideosFromVideo($video['id'])) { ?>
    <div class="row bgWhite list-group-item menusDiv" id="downloadDiv">
        <div class="tabbable-panel">
            <div class="list-group">
                <?php
                foreach ($filesToDownload as $theLink) {
                    ?>
                    <a href="<?php echo $theLink['url']; ?>" class="list-group-item list-group-item-action" target="_blank">
                        <i class="fas fa-download"></i> <?php echo $theLink['name']; ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $("#downloadDiv").slideUp();
            $("#downloadBtn").click(function () {
                $(".menusDiv").not("#downloadDiv").slideUp();
                $("#downloadDiv").slideToggle();
                return false;
            });
        });
    </script>
<?php } ?>
<?php if ($video['type']!=='notfound' && CustomizeUser::canShareVideosFromVideo($video['id'])) { ?>
    <div class="row bgWhite list-group-item menusDiv" id="shareDiv">
        <div class="tabbable-panel">
            <div class="tabbable-line text-muted">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link " href="#tabShare" data-toggle="tab">
                            <span class="fa fa-share"></span>
                            <?php echo __("Share"); ?>
                        </a>
                    </li>

                    <?php
                    if (empty($objSecure->disableEmbedMode)) {
                        ?>
                        <li class="nav-item">
                            <a class="nav-link " href="#tabEmbed" data-toggle="tab">
                                <span class="fa fa-code"></span>
                                <?php echo __("Embed"); ?>
                            </a>
                        </li>
                        <?php
                    }
                    if (empty($advancedCustom->disableEmailSharing)) {
                        ?>

                        <li class="nav-item">
                            <a class="nav-link" href="#tabEmail" data-toggle="tab">
                                <span class="fa fa-envelope"></span>
                                <?php echo __("E-mail"); ?>
                            </a>
                        </li>
                        <?php
                    }
                    ?>


                    <li class="nav-item">
                        <a class="nav-link" href="#tabPermaLink" data-toggle="tab">
                            <span class="fa fa-link"></span>
                            <?php echo __("Permanent Link"); ?>
                        </a>
                    </li>
                </ul>
                <div class="tab-content clearfix">
                    <div class="tab-pane active" id="tabShare">
                        <?php
                        $catLink = @$catLink;
                        $url = urlencode(Video::getLink($video['id'], $video['clean_title']));
                        $title = urlencode($video['title']);
                        include $global['systemRootPath'] . 'view/include/social.php';
                        ?>
                    </div>
                    <div class="tab-pane" id="tabEmbed">
                        <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?> (Iframe): <?php echo getButtontCopyToClipboard('textAreaEmbed'); ?></h4> 
                        <textarea class="form-control" style="min-width: 100%" rows="5" id="textAreaEmbed" readonly="readonly"><?php
                            $code = str_replace("{embedURL}", Video::getLink($video['id'], $video['clean_title'], true), $advancedCustom->embedCodeTemplate);
                            echo htmlentities($code);
                            ?>
                        </textarea>
                        <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?> (Object): <?php echo getButtontCopyToClipboard('textAreaEmbedObject'); ?></h4>
                        <textarea class="form-control" style="min-width: 100%" rows="5" id="textAreaEmbedObject" readonly="readonly"><?php
                            $code = str_replace("{embedURL}", Video::getLink($video['id'], $video['clean_title'], true), $advancedCustom->embedCodeTemplateObject);
                            echo htmlentities($code);
                            ?>
                        </textarea>
                    </div>
                    <?php
                    if (empty($advancedCustom->disableEmailSharing)) {
                        ?>
                        <div class="tab-pane" id="tabEmail">
                            <?php if (!User::isLogged()) { ?>
                                <strong>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                                </strong>
                            <?php } else { ?>
                                <form class="well form-horizontal" action="<?php echo $global['webSiteRootURL']; ?>sendEmail" method="post"  id="contact_form">
                                    <fieldset>
                                        <!-- Text input-->
                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                    <input name="email" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control"  type="text">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Text area -->

                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Message"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                                    <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo __("I would like to share this video with you:"); ?> <?php echo Video::getLink($video['id'], $video['clean_title']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-4 control-label"><?php echo __("Type the code"); ?></label>
                                            <div class="col-md-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha?<?php echo time(); ?>" id="captcha"></span>
                                                    <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                                    <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Button -->
                                        <div class="form-group">
                                            <label class="col-md-4 control-label"></label>
                                            <div class="col-md-8">
                                                <button type="submit" class="btn btn-primary" ><?php echo __("Send"); ?> <span class="glyphicon glyphicon-send"></span></button>
                                            </div>
                                        </div>

                                    </fieldset>
                                </form>
                                <script>
                                    $(document).ready(function () {
                                        $('#btnReloadCapcha').click(function () {
                                            $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                                            $('#captchaText').val('');
                                        });
                                        $('#contact_form').submit(function (evt) {
                                            evt.preventDefault();
                                            modal.showPleaseWait();
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>objects/sendEmail.json.php',
                                                data: $('#contact_form').serializeArray(),
                                                type: 'post',
                                                success: function (response) {
                                                    modal.hidePleaseWait();
                                                    if (!response.error) {
                                                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");
                                                    } else {
                                                        swal("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                                                    }
                                                    $('#btnReloadCapcha').trigger('click');
                                                }
                                            });
                                            return false;
                                        });
                                    });
                                </script>
                            <?php } ?>
                        </div>

                        <?php
                    }
                    ?>
                    <div class="tab-pane" id="tabPermaLink">
                        <div class="form-group">
                            <label class="control-label"><?php echo __("Permanent Link") ?></label>
                            <?php
                            getInputCopyToClipboard('linkPermanent', Video::getPermaLink($video['id']));
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php echo __("URL Friendly") ?> (SEO)</label>
                            <?php
                            getInputCopyToClipboard('linkFriendly', Video::getURLFriendly($video['id']));
                            ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php echo __("Current Time") ?> (SEO)</label>
                            <?php
                            getInputCopyToClipboard('linkCurrentTime', Video::getURLFriendly($video['id']));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
    <div class="row bgWhite list-group-item" id="modeYoutubeBottomContentDetails">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-lg-12">
            <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Category"); ?>:</strong></div>
            <div class="col-xs-8 col-sm-10 col-lg-10"><a class="btn btn-xs btn-default"  href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>"><span class="<?php echo $video['iconClass']; ?>"></span> <?php echo $video['category']; ?></a></div>
            <?php
            if (!empty($video['rrating'])) {
                ?>
                <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Rating"); ?>:</strong></div>
                <div class="col-xs-8 col-sm-10 col-lg-10">
                    <?php
                    include $global['systemRootPath'] . 'view/rrating/rating-' . $video['rrating'] . '.php';
                    ?>
                </div>
                <?php
            }
            if ($video['type']!=='notfound' && $video['type'] !== 'article') {
                ?>
                <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Description"); ?>:</strong></div>
                <div class="col-xs-8 col-sm-10 col-lg-10" itemprop="description">
                    <?php echo $video['description'];
                    if (strpos($video['description'], '<br') !== false || strpos($video['description'], '<p') !== false) {
                        //echo $video['description'];
                    } else {
                        //echo nl2br(textToLink(htmlentities($video['description'])));
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

</div>
<script>
    $(document).ready(function () {
<?php
if (empty($advancedCustom->showShareMenuOpenByDefault)) {
    ?>
            $("#shareDiv").slideUp();
    <?php
}
?>
        $("#shareBtn").click(function () {
            $(".menusDiv").not("#shareDiv").slideUp();
            $("#shareDiv").slideToggle();
            return false;
        });
    });
</script>
<?php
if (!empty($video['id']) && empty($advancedCustom->disableComments) && Video::showYoutubeModeOptions()) {
    ?>
    <div class="row bgWhite list-group-item">
        <?php include $global['systemRootPath'] . 'view/videoComments.php'; ?>
    </div>
    <?php
}
?>