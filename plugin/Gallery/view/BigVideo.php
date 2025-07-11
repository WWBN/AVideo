<?php
if (empty($crc)) {
    $crc = uniqid();
}
$suggestedOrPinnedFound = false;
if ($obj->BigVideo && empty($_GET['showOnly'])) {

    $_REQUEST['rowCount'] = 20;
    unsetCurrentPage();
    if(!empty($global['isChannel'])){
        $videoRows = Video::getAllVideos(Video::SORT_TYPE_CHANNELSUGGESTED, $global['isChannel']);
    }

    if (empty($videoRows) && !empty($obj->useSuggestedVideosAsCarouselInBigVideo)) {
        //getAllVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
        //$videoRows = Video::getAllVideosLight(Video::SORT_TYPE_VIEWABLE, !$obj->hidePrivateVideos, false, true);
        //$_REQUEST['rowCount'] = 20;
        //unsetCurrentPage();
        $videoRows = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, !$obj->hidePrivateVideos, array(), false, false, true, true);
    }
    resetCurrentPage();
    if (empty($videoRows)) {
        $videoRows = array($video);
    }else{
        $suggestedOrPinnedFound = true;
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

    <div class="clearfix" style="margin: 5px 0 20px 0;">
        <div id="bigVideoCarousel" class="<?php echo $class; ?> " data-ride="carousel">
            <?php
            if (count($videoRows) > 1) {
            ?>
                <!-- Indicators -->
                <ol class="carousel-indicators" style="bottom: -25px;">
                    <?php
                    for ($i = 0; $i < count($videoRows); $i++) {
                    ?>
                    <li data-target="#bigVideoCarousel" data-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? "active" : ""; ?>" style="border-color: #DDD;"></li>
                    <?php
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
                                <div class="<?php echo $colClass1; ?>">
                                    <?php
                                    echo Video::getVideoImagewithHoverAnimationFromVideosId($videoRow, true, true, false, true, empty($obj->GifOnBigVideo));
                                    ?>
                                    <?php
                                    if (!empty($program) && $videoRow['type'] == 'serie' && !empty($videoRow['serie_playlists_id'])) {
                                    ?>
                                        <div class="gallerySerieOverlay"
                                        style="pointer-events: none;" >
                                        <!-- BigVideo -->
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
                                    <?php }
                                    ?>
                                </div>
                                <div class="<?php echo $colClass2; ?>">
                                    <div class="<?php echo $colClass3; ?>">
                                        <a class="h6 galleryLink <?php echo $isserieClass; ?>" videos_id="<?php echo $videoRow['id']; ?>" href="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], false, $get); ?>" embed="<?php echo Video::getLink($videoRow['id'], $videoRow['clean_title'], true, $get); ?>" title="<?php echo htmlentities(getSEOTitle($videoRow['title'], 200)); ?>">
                                            <h2 style="margin: 5px 0; padding: 5px 0;"><?php echo getSEOTitle($videoRow['title']); ?></h2>
                                        </a>
                                        <div class="descriptionArea">
                                            <div class="descriptionAreaPreContent">
                                                <div class="descriptionAreaContent">
                                                    <?php
                                                    echo Video::htmlDescription($videoRow['description']);
                                                    ?>
                                                </div>
                                            </div>
                                            <button onclick="$(this).closest('.descriptionArea').toggleClass('expanded');" class="btn btn-xs btn-default descriptionAreaShowMoreBtn" style="display: none; ">
                                                <span class="showMore"><i class="fas fa-caret-down"></i> <?php echo __("Show More"); ?></span>
                                                <span class="showLess"><i class="fas fa-caret-up"></i> <?php echo __("Show Less"); ?></span>
                                            </button>
                                        </div>
                                        <div class="galeryDetails">
                                            <div class="galleryTags">
                                                <?php
                                                if (empty($_REQUEST['catName']) && !empty($obj->showCategoryTag)) {
                                                ?>
                                                    <a class="label label-default" href="<?php echo "{$global['webSiteRootURL']}cat/{$videoRow['clean_category']}"; ?>" data-toggle="tooltip" title="<?php echo $videoRow['category']; ?>">
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
                                            <?php
                                            if (!empty($advancedCustom->showCreationTimeOnVideoItem)) {
                                            ?>
                                                <div>
                                                    <i class="far fa-clock"></i>
                                                    <?php echo humanTimingOrDate(strtotime($videoRow['videoCreation']), 0, true, true); ?>
                                                </div>
                                            <?php
                                            }else{
                                                echo '<!-- empty showCreationTimeOnVideoItem '.basename(__FILE__).' line='.__LINE__.'-->';
                                            }
                                            ?>
                                            <?php
                                            if (!empty($advancedCustom->showChannelNameOnVideoItem)) {
                                            ?>
                                                <div>
                                                    <a href="<?php echo User::getChannelLink($videoRow['users_id']); ?>">
                                                        <i class="fa fa-user"></i>
                                                        <?php echo $name; ?>
                                                    </a>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                            <?php if (Video::canEdit($videoRow['id'])) { ?>
                                                <button type="button" class="btn-link" onclick="avideoModalIframe(webSiteRootURL + 'view/managerVideosLight.php?avideoIframe=1&videos_id=<?php echo $videoRow['id']; ?>');return false;">
                                                    <i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?>
                                                </button>
                                            <?php } ?>
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
                                                        <button type="button" class="btn btn-default btn-sm btn-xs" data-toggle="dropdown">
                                                            <i class="fa fa-download"></i> <?php echo __('Download'); ?> <span class="caret"></span>
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
<?php
} else if (!empty($_GET['showOnly'])) {
?>
    <a href="<?php echo getHomePageURL(); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
<?php
}
