<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
if (empty($video) && !empty($_GET['videos_id'])) {
    $video = Video::getVideo(intval($_GET['videos_id']), "viewable", true, false, true, true);
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
if(empty($video['created'])){
    return false;
}
if(User::hasBlockedUser($video['users_id'])){
    return false;
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
                            $notAllowedKeys = array('m3u8');
                            if (empty($advancedCustom->showImageDownloadOption)) {
                                $notAllowedKeys = array_merge($notAllowedKeys, array('jpg', 'gif', 'webp', 'pjpg'));
                            }
                            $keyFound = false;
                            foreach ($notAllowedKeys as $notAllowedKey) {
                                if(preg_match("/{$notAllowedKey}/", $key)){
                                    $keyFound = true;
                                    break;
                                }
                            }
                            if($keyFound){
                                continue;
                            }
                            if (strpos($theLink['url'], '?') === false) {
                                $theLink['url'] .= "?download=1&title=" . urlencode($video['title'] . "_{$key}_.mp4");
                            }
                            $parts = explode("_", $key);
                            $name = $key;
                            if(count($parts)>1){
                                $name = strtoupper($parts[0]);
                                if(is_numeric($parts[1])){
                                    $name .= " <div class='label label-primary'>{$parts[1]}p</div> ".getResolutionLabel($parts[1]);
                                }else{
                                    $name .= " <div class='label label-primary'>".strtoupper($parts[1])."</div> ";
                                }
                            }
                            
                            $filesToDownload[] = array('name' => $name, 'url' => $theLink['url']);
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
            <div class="list-group list-group-horizontal">
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
<?php } 

if ($video['type']!=='notfound' && CustomizeUser::canShareVideosFromVideo($video['id'])) {
    getShareMenu($video['title'], Video::getPermaLink($video['id']), Video::getURLFriendly($video['id']), Video::getLink($video['id'], $video['clean_title'], true));
} 
?>
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
                    <?php 
                    echo Video::htmlDescription($video['description']);
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