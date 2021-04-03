<?php
if (empty($crc)) {
    $crc = uniqid();
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {

    if (!empty($obj->useSuggestedVideosAsCarouselInBigVideo)) {
        //getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
        //$videoRows = Video::getAllVideosLight("viewable", !$obj->hidePrivateVideos, false, true);
        $_REQUEST['rowCount'] = 20;
        $_REQUEST['current'] = 1;
        $videoRows = Video::getAllVideos("viewable", false, !$obj->hidePrivateVideos, array(), false, false, true, true);
    }
    if (empty($videoRows)) {
        $videoRows = array($video);
    }
    $class = '';
    $classInner = '';
    if (count($videoRows) > 1) {
        $class = 'carousel slide';
        $classInner = 'carousel-inner';
    }
    ?>
    <style>
        #bigVideoCarousel .carousel-indicators .active {
            border-color: #777 !important;
        }
    </style>
    <div class="row">
        <div class="col-sm-12 fixPadding">
            <div id="bigVideoCarousel" class="<?php echo $class; ?> " data-ride="carousel">
                <?php
                if (count($videoRows) > 1) {
                    ?>
                    <!-- Indicators -->
                    <ol class="carousel-indicators" style="bottom: -25px;">
                        <?php
                        for ($i = 0; $i < count($videoRows); $i++) {
                            ?><li data-target="#bigVideoCarousel" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? "active" : ""; ?>" style="border-color: #DDD;"></li><?php
                        }
                        ?>
                    </ol>
                    <?php
                }
                ?>
                <!-- Wrapper for slides -->
                <div class="<?php echo $classInner; ?>">
                    <?php
                    $count = 0;
                    $program = AVideoPlugin::loadPluginIfEnabled('PlayLists');
                    foreach ($videoRows as $videoRow) {
                        $count++;
                        $category = new Category($videoRow['categories_id']);
                        $videoRow['category'] = $category->getName();
                        $videoRow['clean_category'] = $category->getClean_name();
                        $videoRow['iconClass'] = $category->getIconClass();
                        $videoRow['videoCreation'] = $videoRow['created'];
                        $name = User::getNameIdentificationById($videoRow['users_id']);
                        if (empty($get)) {
                            $get = array();
                        }
                        $bigVideoAd = getAdsLeaderBoardBigVideo();
                        $colClass1 = "col-sm-5";
                        $colClass2 = "col-sm-7";
                        $colClass3 = "";
                        if (!empty($bigVideoAd)) {
                            $colClass1 = "col-sm-4";
                            $colClass2 = "col-sm-8";
                            $colClass3 = "col-sm-6";
                        }
                        $isserie = Video::isSerie($videoRow['id']);

                        $isserieClass = "";
                        if ($isserie) {
                            $isserieClass = "isserie";
                        }
                        ?>
                        <div class="item <?php echo $count === 1 ? "active" : ""; ?>">
                            <div class="clear clearfix">
                                <div class="row thumbsImage">
                                    <div class="<?php echo $colClass1; ?> galleryVideo">
                                        <a class="galleryLink <?php echo $isserieClass; ?>" videos_id="<?php echo $videoRow['id']; ?>" 
                                           href="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], false, $get); ?>" 
                                           embed="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], true, $get); ?>" 
                                           title="<?php echo $videoRow['title']; ?>" style="">
                                               <?php
                                               $images = Video::getImageFromFilename($videoRow['filename'], $videoRow['type']);
                                               $imgGif = $images->thumbsGif;
                                               $poster = isMobile() ? $images->thumbsJpg : $images->poster;
                                               ?>
                                            <div class="aspectRatio16_9">
                                                <img src="<?php echo $images->thumbsJpgSmall; ?>" data-src="<?php echo $poster; ?>" alt="<?php echo $videoRow['title']; ?>" class="thumbsJPG img img-responsive <?php echo ($poster != $images->thumbsJpgSmall && !empty($advancedCustom->usePreloadLowResolutionImages)) ? "blur" : ""; ?>" style="height: auto; width: 100%;" id="thumbsJPG<?php echo $videoRow['id']; ?>" />
                                                <?php if (!empty($obj->GifOnBigVideo) && !empty($imgGif)) { ?>
                                                    <img src="<?php echo getCDN(); ?>view/img/loading-gif.png" data-src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $videoRow['title']; ?>" id="thumbsGIF<?php echo $videoRow['id']; ?>" class="thumbsGIF img-responsive <?php echo @$img_portrait; ?>  rotate<?php echo $videoRow['rotation']; ?>" height="130" />
                                                <?php } ?>
                                            </div>
                                            <?php
                                            if (isToShowDuration($videoRow['type'])) {
                                                ?>
                                                <span class="duration"><?php echo Video::getCleanDuration($videoRow['duration']); ?></span>
                                                <div class="progress" style="height: 3px; margin-bottom: 2px;">
                                                    <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $videoRow['progress']['percent'] ?>%;" aria-valuenow="<?php echo $videoRow['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <?php
                                            }

                                            if (!empty($program) && $videoRow['type'] == 'serie' && !empty($videoRow['serie_playlists_id'])) {
                                                ?>
                                                <div class="gallerySerieOverlay">
                                                    <div class="gallerySerieOverlayTotal">
                                                        <?php
                                                        $plids = PlayList::getVideosIDFromPlaylistLight($videoRow['serie_playlists_id']);
                                                        echo count($plids);
                                                        ?>
                                                        <br><i class="fas fa-list"></i>
                                                    </div>
                                                    <i class="fas fa-play"></i>
                                                    <?php
                                                    echo __("Play All");
                                                    ?>
                                                </div>
                                                <?php
                                            } else
                                            if (User::isLogged() && !empty($program)) {
                                                ?>
                                                <div class="galleryVideoButtons" style="margin-right: 20px;">
                                                    <?php
                                                    //var_dump($value['isWatchLater'], $value['isFavorite']);
                                                    if ($videoRow['isWatchLater']) {
                                                        $watchLaterBtnAddedStyle = "";
                                                        $watchLaterBtnStyle = "display: none;";
                                                    } else {
                                                        $watchLaterBtnAddedStyle = "display: none;";
                                                        $watchLaterBtnStyle = "";
                                                    }
                                                    if ($videoRow['isFavorite']) {
                                                        $favoriteBtnAddedStyle = "";
                                                        $favoriteBtnStyle = "display: none;";
                                                    } else {
                                                        $favoriteBtnAddedStyle = "display: none;";
                                                        $favoriteBtnStyle = "";
                                                    }
                                                    ?>

                                                    <button onclick="addVideoToPlayList(<?php echo $videoRow['id']; ?>, false, <?php echo $videoRow['watchLaterId']; ?>);return false;" class="btn btn-dark btn-xs watchLaterBtnAdded watchLaterBtnAdded<?php echo $videoRow['id']; ?>" title="<?php echo __("Added On Watch Later"); ?>" style="color: #4285f4;<?php echo $watchLaterBtnAddedStyle; ?>" ><i class="fas fa-check"></i></button> 
                                                    <button onclick="addVideoToPlayList(<?php echo $videoRow['id']; ?>, true, <?php echo $videoRow['watchLaterId']; ?>);return false;" class="btn btn-dark btn-xs watchLaterBtn watchLaterBtn<?php echo $videoRow['id']; ?>" title="<?php echo __("Watch Later"); ?>" style="<?php echo $watchLaterBtnStyle; ?>" ><i class="fas fa-clock"></i></button>
                                                    <br>
                                                    <button onclick="addVideoToPlayList(<?php echo $videoRow['id']; ?>, false, <?php echo $videoRow['favoriteId']; ?>);return false;" class="btn btn-dark btn-xs favoriteBtnAdded favoriteBtnAdded<?php echo $videoRow['id']; ?>" title="<?php echo __("Added On Favorite"); ?>" style="color: #4285f4; <?php echo $favoriteBtnAddedStyle; ?>"><i class="fas fa-check"></i></button>  
                                                    <button onclick="addVideoToPlayList(<?php echo $videoRow['id']; ?>, true, <?php echo $videoRow['favoriteId']; ?>);return false;" class="btn btn-dark btn-xs favoriteBtn favoriteBtn<?php echo $videoRow['id']; ?>" title="<?php echo __("Favorite"); ?>" style="<?php echo $favoriteBtnStyle; ?>" ><i class="fas fa-heart" ></i></button>    

                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </a>
                                    </div>
                                    <div class="<?php echo $colClass2; ?>">
                                        <div class="<?php echo $colClass3; ?>">
                                            <a class="h6 galleryLink <?php echo $isserieClass; ?>" videos_id="<?php echo $videoRow['id']; ?>" 
                                               href="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], false, $get); ?>" 
                                               embed="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], true, $get); ?>" 
                                               title="<?php echo $videoRow['title']; ?>">
                                                <h1><?php echo $videoRow['title']; ?></h1>
                                            </a>
                                            <div class="mainAreaDescriptionContainer">
                                                <h4 class="mainAreaDescription" itemprop="description"><?php
                                                    if (strpos($videoRow['description'], '<br') !== false || strpos($videoRow['description'], '<p') !== false) {
                                                        echo $videoRow['description'];
                                                    } else {
                                                        echo nl2br(textToLink(htmlentities($videoRow['description'])));
                                                    }
                                                    ?></h4>
                                            </div>
                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php if (empty($_GET['catName'])) { ?>
                                                        <a class="label label-default" href="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], false, $get); ?>">
                                                            <?php
                                                            if (!empty($videoRow['iconClass'])) {
                                                                ?>
                                                                <i class="<?php echo $videoRow['iconClass']; ?>"></i>
                                                                <?php
                                                            }
                                                            ?>
                                                            <?php echo $videoRow['category']; ?>
                                                        </a>
                                                    <?php } ?>
                                                    <?php
                                                    if (!empty($obj->showTags)) {
                                                        $videoRow['tags'] = Video::getTags($videoRow['id']);
                                                        if (!empty($videoRow['tags'])) {
                                                            foreach ($videoRow['tags'] as $value2) {
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
                                                    if (AVideoPlugin::isEnabledByName('LiveUsers')) {
                                                        echo getLiveUsersLabelVideo($videoRow['id'], $videoRow['views_count'], "", "");
                                                    } else {
                                                        ?>
                                                        <div>
                                                            <i class="fa fa-eye"></i>
                                                            <span itemprop="interactionCount">
                                                                <?php echo number_format($videoRow['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                            </span>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <div>
                                                    <i class="far fa-clock"></i>
                                                    <?php echo humanTiming(strtotime($videoRow['videoCreation'])), " ", __('ago'); ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-user"></i>
                                                    <a class="text-muted" href="<?php echo User::getChannelLink($videoRow['users_id']); ?>">
                                                        <?php echo $name; ?>
                                                    </a>
                                                </div>
                                                <?php if (Video::canEdit($videoRow['id'])) { ?>
                                                    <div>
                                                        <a href="#" onclick="avideoModalIframe('<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $videoRow['id']; ?>');return false;" class="text-primary"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                                    </div>
                                                <?php } ?>
                                                <?php if (!empty($videoRow['trailer1'])) { ?>
                                                    <div>
                                                        <span onclick="showTrailer('<?php echo parseVideos($videoRow['trailer1'], 1); ?>'); return false;" class="text-primary cursorPointer" >
                                                            <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
                                                        </span>
                                                    </div>
                                                <?php }
                                                ?>
                                                <?php
                                                echo AVideoPlugin::getGalleryActionButton($videoRow['id']);
                                                ?>
                                                <?php
                                                if (CustomizeUser::canDownloadVideosFromVideo($videoRow['id'])) {

                                                    @$timesG[__LINE__] += microtime(true) - $startG;
                                                    $startG = microtime(true);
                                                    $files = getVideosURL($videoRow['filename']);
                                                    @$timesG[__LINE__] += microtime(true) - $startG;
                                                    $startG = microtime(true);
                                                    if (!empty($files['mp4']) || !empty($files['mp3'])) {
                                                        ?>
                                                        <div style="position: relative; overflow: visible;">
                                                            <button type="button" class="btn btn-default btn-sm btn-xs"  data-toggle="dropdown">
                                                                <i class="fa fa-download"></i> <?php echo!empty($advancedCustom->uploadButtonDropdownText) ? $advancedCustom->uploadButtonDropdownText : ""; ?> <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu" role="menu">
                                                                <?php
                                                                //var_dump($files);exit;
                                                                foreach ($files as $key => $theLink) {
                                                                    if ($theLink['type'] !== 'video' && $theLink['type'] !== 'audio' || $key == "m3u8") {
                                                                        continue;
                                                                    }
                                                                    $path_parts = pathinfo($theLink['filename']);
                                                                    ?>
                                                                    <li>
                                                                        <a href="<?php echo $theLink['url']; ?>?download=1&title=<?php echo urlencode($videoRow['title'] . "_{$key}_.{$path_parts['extension']}"); ?>">
                                                                            <?php echo __("Download"); ?> <?php echo $key; ?>
                                                                        </a>
                                                                    </li>
                                                                <?php }
                                                                ?>
                                                            </ul>
                                                        </div>
                                                        <?php
                                                    }
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
