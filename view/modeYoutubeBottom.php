<?php
if (empty($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/subscribe.php';
if ((empty($video) || !is_array($video) ) && !empty($_GET['videos_id'])) {
    $video = Video::getVideo(intval($_GET['videos_id']), "viewable", true, false, true, true);
    $created = !empty($video['videoCreation'])?$video['videoCreation']:$video['created'];
    $video['creator'] = Video::getCreatorHTML($video['users_id'], '<div class="clearfix"></div><small>' . humanTiming(_strtotime($created)) . '</small>');
    $source = Video::getSourceFile($video['filename']);
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } elseif ($video['type'] == "audio") {
        $img = "" . getURL("view/img/audio_wave.jpg");
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    } elseif ($video['type'] === 'zip') {
        $type = 'zip';
    } elseif ($video['type'] === 'article') {
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
if (empty($video['created']) || !is_array($video)) {
    return false;
}
if (User::hasBlockedUser($video['users_id'])) {
    return false;
}

$objGallery = AVideoPlugin::getObjectData("Gallery");
$cdnObj = AVideoPlugin::getDataObjectIfEnabled('CDN');
$cdnStorageEnabled = !empty($cdnObj) && $cdnObj->enable_storage;

$description = getSEODescription(emptyHTML($video['description']) ? $video['title'] : $video['description']);
?>



<div class="panel panel-default">
    <div class="panel-body">
        <?php
        $tags = Video::getSeoTags($video['id']);
        echo $tags['body'];
        ?>
        <?php
        if (!empty($video['id']) && Video::showYoutubeModeOptions() && Video::canEdit($video['id'])) {
            ?>
            <div class="btn-group pull-right" role="group" aria-label="Botttom Buttons">
                <button type="button" class="btn btn-primary btn-xs"  onclick="avideoModalIframe(webSiteRootURL + 'view/managerVideosLight.php?avideoIframe=1&videos_id=<?php echo $video['id']; ?>');return false;" data-toggle="tooltip" title="<?php echo __("Edit Video"); ?>">
                    <i class="fa fa-edit"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Edit Video"); ?></span>
                </button>
                <button type="button" class="btn btn-default btn-xs" onclick="avideoModalIframeFull(webSiteRootURL + 'view/videoViewsInfo.php?videos_id=<?php echo $video['id']; ?>');
                        return false;">
                    <i class="fa fa-eye"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Views Info"); ?></span>
                </button>
                <?php
                echo Layout::getSuggestedButton($video['id']);
                ?>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="panel-body">
        <div class="row divMainVideo">
            <div class="col-xs-4 col-sm-4 col-md-4">
                <?php
                echo Video::getVideoImagewithHoverAnimationFromVideosId($video, true, false);
                ?>

                <!-- modeYouTubeBottom plugins tags -->
                <?php
                echo Video::getTagsHTMLLabelIfEnable($video['id']);
                ?>
                <!-- modeYouTubeBottom end plugins tags -->
            </div>
            <div class="col-xs-8 col-sm-8 col-md-8">
                <?php echo $video['creator']; ?>

                <?php
                if (Video::showYoutubeModeOptions() && empty($advancedCustom->doNotDisplayViews)) {
                    ?>
                    <span class="watch-view-count pull-right text-muted" itemprop="interactionCount"><span class="view-count<?php echo $video['id']; ?>"><?php echo number_format_short($video['views_count']); ?></span> <?php echo __("Views"); ?></span>
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
    </div>
    <div class="panel-footer">

        <?php
        if (Video::showYoutubeModeOptions()) {
            ?>
            <div class="row">
                <div class="col-md-12 text-muted">
                    <?php if (empty($advancedCustom->disableShareAndPlaylist)) { ?>
                        <?php if (CustomizeUser::canShareVideosFromVideo($video['id'])) { ?>
                            <a href="#" class="btn btn-default no-outline" id="shareBtn">
                                <span class="fa fa-share"></span>
                                <span class="hidden-sm hidden-xs"><?php echo __("Share"); ?></span>
                            </a>
                            <?php
                        }
                        $filesToDownload = [];
                        $files = [];
                        $canDownloadFiles = CustomizeUser::canDownloadVideosFromVideo($video['id']);
                        if ($video['type'] == "zip") {
                            $files = getVideosURLZIP($video['filename']);
                        } else if ($video['type'] == "pdf") {
                            $files = getVideosURLPDF($video['filename']);
                            ;
                        } else if ($canDownloadFiles) {
                            $files = getVideosURL($video['filename']);
                        }
                        if (!empty($files)) {
                            $downloadMP3Link = [];
                            $downloadMP4Link = [];
                            foreach ($files as $key => $theLink) {
                                //$notAllowedKeys = array('m3u8');
                                $notAllowedKeys = ['log'];
                                if (empty($advancedCustom->showImageDownloadOption)) {
                                    $notAllowedKeys = array_merge($notAllowedKeys, ['jpg', 'gif', 'webp', 'pjpg']);
                                }
                                $keyFound = false;
                                foreach ($notAllowedKeys as $notAllowedKey) {
                                    if (preg_match("/{$notAllowedKey}/i", $key)) {
                                        $keyFound = true;
                                        break;
                                    }
                                }
                                if ($keyFound) {
                                    continue;
                                }

                                if (!$cdnStorageEnabled || !preg_match('/cdn\.ypt\.me(.*)\.m3u8/i', $theLink['url'])) {
                                    $theLink['url'] = addQueryStringParameter($theLink['url'], "download", 1);
                                    $theLink['url'] = addQueryStringParameter($theLink['url'], "title", getSEOTitle($video['title']) . "_{$key}_." . ($video['type'] === 'audio' ? 'mp3' : 'mp4'));

                                    if (!$cdnStorageEnabled && $key == 'm3u8') {
                                        $name = 'MP4';
                                    } else {
                                        $parts = explode("_", $key);
                                        $name = $key;
                                        if (count($parts) > 1) {
                                            $name = strtoupper($parts[0]);
                                            if (is_numeric($parts[1])) {
                                                $name .= " <div class='label label-primary'>{$parts[1]}p</div> " . getResolutionLabel($parts[1]);
                                            } else {
                                                $name .= " <div class='label label-primary'>" . strtoupper($parts[1]) . "</div> ";
                                            }
                                        }
                                    }


                                    $filesToDownload[] = ['name' => $name, 'url' => $theLink['url']];
                                }
                            }

                            if ($canDownloadFiles) {
                                $filesToDownload = array_merge($filesToDownload, getMP3ANDMP4DownloadLinksFromHLS($videos_id, $video['type']));
                            }

                            if (!empty($filesToDownload)) {
                                ?>
                                <a href="#" class="btn btn-default no-outline" id="downloadBtn">
                                    <span class="fa fa-download"></span>
                                    <span class="hidden-sm hidden-xs"><?php echo __("Download"); ?></span>
                                </a>
                                <?php
                            } else {
                                echo '<!-- files to download are empty -->';
                            }
                        } else {
                            echo '<!-- CustomizeUser::canDownloadVideosFromVideo said NO -->';
                        }
                        ?>
                        <?php
                    }
                    $_v = $video;
                    echo AVideoPlugin::getWatchActionButton($video['id']);
                    $video = $_v;
                    ?>
                    <?php
                    if (!empty($video['id']) && empty($advancedCustom->removeThumbsUpAndDown)) {
                        ?>
                        <a href="#" class="faa-parent animated-hover btn btn-default no-outline pull-right <?php echo (@$video['myVote'] == - 1) ? "myVote" : "" ?>" id="dislikeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("Don´t like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                            <span class="fa fa-thumbs-down faa-bounce faa-reverse "></span> <small><?php echo $video['dislikes']; ?></small>
                        </a>
                        <a href="#" class="faa-parent animated-hover btn btn-default no-outline pull-right <?php echo (@$video['myVote'] == 1) ? "myVote" : "" ?>" id="likeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("Like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                            <span class="fa fa-thumbs-up faa-bounce"></span>
                            <small><?php echo $video['likes']; ?></small>
                        </a>
                        <script>
                            $(document).ready(function () {
        <?php if (User::isLogged()) { ?>
                                    $("#dislikeBtn, #likeBtn").click(function () {
                                        $.ajax({
                                            url: webSiteRootURL + ($(this).attr("id") == "dislikeBtn" ? "dislike" : "like"),
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

                    <?php }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="panel-footer" style="padding: 0;">
        <?php if (!empty($filesToDownload)) { ?>
            <div class="row bgWhite list-group-item menusDiv" id="downloadDiv">
                <div class="tabbable-panel">
                    <div class="list-group list-group-horizontal">
                        <?php
                        foreach ($filesToDownload as $theLink) {
                            if (empty($theLink)) {
                                continue;
                            }
                            if (preg_match('/\.json/i', $theLink['url'])) {
                                ?>
                                <button type="button" onclick="downloadURLOrAlertError('<?php echo $theLink['url']; ?>', {}, '<?php echo $video['clean_title']; ?>.<?php echo strtolower($theLink['name']); ?>', '<?php echo $theLink['progress']; ?>');"
                                        class="btn btn-default" target="_blank">
                                    <i class="fas fa-download"></i> <?php echo $theLink['name']; ?>
                                </button>
                                <?php
                            } else {
                                ?>
                                <a href="<?php echo $theLink['url']; ?>" class="list-group-item list-group-item-action" target="_blank">
                                    <i class="fas fa-download"></i> <?php echo $theLink['name']; ?>
                                </a>
                                <?php
                            }
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
            <?php
        }

        if ($video['type'] !== 'notfound' && CustomizeUser::canShareVideosFromVideo($video['id'])) {
            $bitLyLink = false;
            if (AVideoPlugin::isEnabledByName('BitLy')) {
                $bitLyLink = BitLy::getLink($video['id']);
            }

            getShareMenu($video['title'], Video::getPermaLink($video['id']), Video::getURLFriendly($video['id']), Video::getLink($video['id'], $video['clean_title'], true), $img, "row bgWhite list-group-item menusDiv", parseDurationToSeconds($video['duration']), $bitLyLink);
        }
        ?>
    </div>
    <div class="panel-body" id="modeYoutubeBottomContentDetails">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-12">
                <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Category"); ?>:</strong></div>
                <div class="col-xs-8 col-sm-10 col-lg-10"><a class="btn btn-xs btn-default"  href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo @$video['clean_category']; ?>"><span class="<?php echo @$video['iconClass']; ?>"></span> <?php echo @$video['category']; ?></a></div>

                <?php
                if (!empty($video['rrating'])) {
                    ?>

                    <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Rating"); ?>:</strong></div>
                    <div class="col-xs-8 col-sm-10 col-lg-10">
                        <img src="<?php echo getURL('view/rrating/rating-' . $video['rrating'] . '.png'); ?>"
                             class="img img-responsive zoom"
                             style="width:30px;"/>
                    </div>

                    <?php
                }
                ?>
                <?php
                if (!empty($advancedCustom->showVideoDownloadedLink) && isValidURL($video['videoDownloadedLink'])) {
                    $parse = parse_url($video['videoDownloadedLink']);
                    $domain = str_replace('www.', '', $parse['host']);
                    ?>
                    <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Source"); ?>:</strong></div>
                    <div class="col-xs-8 col-sm-10 col-lg-10 descriptionArea" itemprop="source">
                        <a class="btn btn-xs btn-default" href="<?php echo $video['videoDownloadedLink']; ?>" target="_blank" rel="nofollow">
                            <i class="fas fa-external-link-alt"></i>
                            <?php
                            echo $domain;
                            ?>
                        </a>
                    </div>
                    <?php
                }
                if ($video['type'] !== 'notfound' && $video['type'] !== 'article' && !isHTMLEmpty($video['description'])) {
                    ?>
                    <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Description"); ?>:</strong></div>
                    <div class="col-xs-8 col-sm-10 col-lg-10 descriptionArea" itemprop="description">
                        <?php
                        if (empty($advancedCustom->disableShowMOreLessDescription)) {
                            ?>
                            <div class="descriptionAreaPreContent">
                                <div class="descriptionAreaContent">
                                    <?php echo Video::htmlDescription($video['description']); ?>
                                </div>
                            </div>
                            <button onclick="$(this).closest('.descriptionArea').toggleClass('expanded');" class="btn btn-xs btn-default descriptionAreaShowMoreBtn" style="display: none; ">
                                <span class="showMore"><i class="fas fa-caret-down"></i> <?php echo __("Show More"); ?></span>
                                <span class="showLess"><i class="fas fa-caret-up"></i> <?php echo __("Show Less"); ?></span>
                            </button>
                            <?php
                        } else {
                            echo Video::htmlDescription($video['description']);
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    if (!empty($video['id']) && empty($advancedCustom->disableComments) && Video::showYoutubeModeOptions()) {
        ?>
        <div class="panel-footer" id="modeYoutubeBottomContentDetails">
            <?php include $global['systemRootPath'] . 'view/videoComments.php'; ?>
        </div>
        <?php
    }
    ?>
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
