<?php
if (empty($crc)) {
    $crc = uniqid();
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    $name = User::getNameIdentificationById($video['users_id']);
    if (empty($get)) {
        $get = array();
    }
    $bigVideoAd = getAdsLeaderBoardBigVideo();
    $colClass1 = "col-sm-6";
    $colClass2 = "col-sm-6";
    $colClass3 = "";
    if(!empty($bigVideoAd)){
        $colClass1 = "col-sm-4";
        $colClass2 = "col-sm-8";
        $colClass3 = "col-sm-6";
    }
    ?>
    <div class="clear clearfix">
        <div class="row thumbsImage">
            <div class="<?php echo $colClass1; ?>">
                <a class="galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo Video::getLink($video['id'], $video['clean_title'], false, $get); ?>" title="<?php echo $video['title']; ?>" style="">
                    <?php
                    $images = Video::getImageFromFilename($video['filename'], $video['type']);
                    $imgGif = $images->thumbsGif;
                    $poster = $images->poster;
                    ?>
                    <div class="aspectRatio16_9">
                        <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="thumbsJPG img img-responsive <?php echo ($poster != $images->thumbsJpgSmall) ? "blur" : ""; ?>" style="height: auto; width: 100%;" id="thumbsJPG<?php echo $video['id']; ?>" />
                        <?php if (!empty($obj->GifOnBigVideo) && !empty($imgGif)) { ?>
                            <img src="<?php echo $global['webSiteRootURL']; ?>view/img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $video['title']; ?>" id="thumbsGIF<?php echo $video['id']; ?>" class="thumbsGIF img-responsive <?php echo @$img_portrait; ?>  rotate<?php echo $video['rotation']; ?>" height="130" />
                        <?php } ?>
                    </div>
                    <?php
                    if ($video['type'] !== 'pdf' && $video['type'] !== 'article') {
                        ?>
                        <span class="duration"><?php echo Video::getCleanDuration($video['duration']); ?></span>
                        <div class="progress" style="height: 3px; margin-bottom: 2px;">
                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $video['progress']['percent'] ?>%;" aria-valuenow="<?php echo $video['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <?php
                    }
                    ?>
                </a>
            </div>
            <div class="<?php echo $colClass2; ?>">
                <div class="<?php echo $colClass3; ?>">
                    <a class="h6 galleryLink" videos_id="<?php echo $video['id']; ?>" href="<?php echo Video::getLink($video['id'], $video['clean_title'], false, $get); ?>" title="<?php echo $video['title']; ?>">
                        <h1><?php echo $video['title']; ?></h1>
                    </a>
                    <div class="mainAreaDescriptionContainer">
                        <h4 class="mainAreaDescription" itemprop="description"><?php echo $video['description']; ?></h4>
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

                        <?php
                        if (empty($advancedCustom->doNotDisplayViews)) {
                            ?>
                            <div>
                                <i class="fa fa-eye"></i>
                                <span itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                            </div>
                        <?php } ?>
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
                        <?php
                        echo YouPHPTubePlugin::getGalleryActionButton($video['id']);
                        ?>
                        <?php
                        if (CustomizeUser::canDownloadVideosFromVideo($video['id'])) {
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
                <div class="<?php echo $colClass3; ?>">
                    <?php echo $bigVideoAd; ?>
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
