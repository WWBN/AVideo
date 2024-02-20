<?php
saveRequestVars();
?>
<link href="<?php echo getURL('plugin/Gallery/style.css'); ?>" rel="stylesheet" type="text/css"/>
<div class="row mainArea">
    <?php
    if (!empty($currentCat)) {
        include $global['systemRootPath'] . 'plugin/Gallery/view/Category.php';
    }
    $obj = AVideoPlugin::getObjectData("Gallery");
    if ($obj->searchOnChannels) {
        echo '<!-- searchOnChannels -->';
        if (!empty($_REQUEST['search'])) {
            $users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();
            $channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");
            if (!empty($channels)) {
    ?>
                <div id="channelsResults" class="clear clearfix">
                    <h3 class="galleryTitle"> <i class="fas fa-user"></i> <?php echo __('Channels'); ?></h3>
                    <div class="row">
                        <?php
                        $search = $_REQUEST['search'];
                        clearSearch();
                        foreach ($channels as $value) {
                            echo '<div class="col-sm-12">';
                            User::getChannelPanel($value['id']);
                            echo '</div>';
                        }
                        reloadSearch();
                        ?>
                    </div>
                </div>
            <?php
                global $contentSearchFound;
                $contentSearchFound = true;
            }
        }
    }
    if (empty($_GET['search']) && !isInfiniteScroll()) {
        include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideoLive.php';
    }
    //var_dump(!empty($video), debug_backtrace());exit;
    if (!empty($video)) {
        global $contentSearchFound;
        $contentSearchFound = true;
        $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
        if (empty($_GET['search']) && !isInfiniteScroll()) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
        }
        echo '<center style="margin:5px;">' . getAdsLeaderBoardTop2() . '</center>';
        if (empty($_REQUEST['catName'])) {
            $objLive = AVideoPlugin::getDataObject('Live');
            if (empty($objLive->doNotShowLiveOnVideosList)) {
            ?>
                <!-- For Live Videos mainArea -->
                <div id="liveVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Schedule Videos -->
                <div id="liveScheduleVideos" class="clear clearfix" style="display: none;">
                    <h3 class="galleryTitle"> <i class="far fa-calendar-alt"></i> <?php echo __($objLive->live_schedule_label); ?></h3>
                    <div class="extraVideos"></div>
                </div>
                <!-- For Live Videos End -->
                <?php
            }
        }
        echo AVideoPlugin::getGallerySection();

        $sections = Gallery::getSectionsOrder();
        $countSections = 0;
        if (!empty($_REQUEST['catName'])) {
            $currentCat = Category::getCategoryByName($_REQUEST['catName']);
            //createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);

            include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaCategory.php';
        } else {
            //var_dump($sections);exit;
            //var_dump(getSearchVar());exit;
            foreach ($sections as $value) {
                if (empty($value['active'])) {
                    continue;
                }
                $countSections++;
                if (preg_match('/Channel_([0-9]+)_/', $value['name'], $matches) && empty($_GET['showOnly'])) {
                    $users_id = intval($matches[1]);
                    User::getChannelPanel($users_id);
                } else
                if ($value['name'] == 'Shorts' && empty($_GET['showOnly']) && AVideoPlugin::isEnabledByName('Shorts')) {
                    include $global['systemRootPath'] . 'plugin/Shorts/row.php';
                } else
                if ($value['name'] == 'Suggested') {
                    createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star", true);
                } else 
                if(empty(getSearchVar()) && empty($_GET['showOnly']) && $value['name'] == 'PlayLists'){
                    $objPl = AVideoPlugin::getDataObject('PlayLists');
                    $plRows = PlayList::getAllToShowOnFirstPage();
                    //var_dump(count($plRows));exit;
                    if (!empty($plRows)) {
                        $rowCount = getRowCount();
                        setRowCount($obj->PlayListsRowCount);
                        foreach ($plRows as $pl) {
                        ?>
                            <!-- For Playlist -->
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <a href="<?php echo "{$global['webSiteRootURL']}viewProgram/{$pl['id']}/" . urlencode($pl['name']); ?>">
                                        <i class="fas fa-list"></i> <?php echo __($pl['name']); ?>
                                    </a>
                                </h3>
                                <?php
                                $videos = PlayList::getAllFromPlaylistsID($pl['id']);
                                // need to add dechex because some times it return an negative value and make it fails on javascript playlists
                                ?>
                                <div class="gallerySectionContent">
                                    <?php
                                    $countCols = createGallerySection($videos);
                                    ?>
                                </div>
                            </div>
                        <?php
                        }
                        setRowCount($rowCount);
                    }
                }else 
                if ($value['name'] == 'Trending') {
                    createGallery(!empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending"), 'trending', $obj->TrendingRowCount, 'TrendingOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-chart-line", true);
                } else
                if ($value['name'] == 'SortByName') {
                    createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-font", true);
                } else
                if ($value['name'] == 'DateAdded' && empty($_REQUEST['catName'])) {
                    createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-calendar-alt", true);
                } else
                if ($value['name'] == 'PrivateContent') {
                    createGallery(!empty($obj->PrivateContentCustomTitle) ? $obj->PrivateContentCustomTitle : __("Private Content"), 'created', $obj->PrivateContentRowCount, 'privateContentOrder', __("Most"), __("Fewest"), $orderString, "DESC", true, "fas fa-lock", true);
                } else
                if ($value['name'] == 'MostWatched') {
                    createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-eye", true);
                } else
                if ($value['name'] == 'MostPopular') {
                    createGallery(!empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular"), 'likes', $obj->MostPopularRowCount, 'mostPopularOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "fas fa-fire", true);
                } else
                if ($value['name'] == 'SubscribedChannels' && User::isLogged() && empty($_GET['showOnly'])) {
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaChannels.php';
                } else
                if ($value['name'] == 'SubscribedTags' && User::isLogged() && empty($_GET['showOnly'])) {
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaTags.php';
                } else
                if ($value['name'] == 'Categories' && empty($_GET['showOnly'])) {
                    if (empty($currentCat) && !empty(getSearchVar())) {
                        $onlySuggested = $obj->CategoriesShowOnlySuggested;
                        cleanSearchVar();
                        $categories = Category::getAllCategories(false, true, $onlySuggested);
                        reloadSearchVar();
                        foreach ($categories as $value) {
                            $currentCat = $value['clean_name'];
                            include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                        }
                    } else {
                        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                    }
                }
            }
            if (empty($countSections) && !empty($_REQUEST['catName'])) {
                $category = Category::getCategoryByName($_REQUEST['catName']);
                createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);
            }
        }
    } else {
        echo '<!-- ' . basename(__FILE__) . ' modeGalleryCategoryLive -->';
        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
        $ob = _ob_get_clean();
        _ob_start();
        echo AVideoPlugin::getGallerySection();
        $ob2 = _ob_get_clean();
        echo $ob;
        global $contentSearchFound;
        if (empty($contentSearchFound) && empty($ob2)) {
            //$contentSearchFound = false;
        } else {
            $contentSearchFound = true;
        }
    }

    global $contentSearchFound;
    if (empty($contentSearchFound)) {
        //var_dump(debug_backtrace(), $debugLastGetVideoSQL);exit;
        _session_start();
        unset($_SESSION['type']);
        ?>
        <div class="alert alert-warning">
            <h1>
                <i class="fa-solid fa-video"></i>
                <?php echo __("Warning"); ?>!
            </h1>
            <!-- <?php echo basename(__FILE__); ?> -->
            <?php echo __("It seems that your search did not return any results"); ?>.
            <br>
            <?php echo __("This could be due to the enabled filters"); ?>:
        </div>
    <?php
        _error_log('contentSearchFound NOT FOUND ' . json_encode(debug_backtrace()));
        _error_log('contentSearchFound NOT FOUND LAST SQL ' . $debugLastGetVideoSQL);
        _error_log('contentSearchFound NOT FOUND LAST TOTAL SQL ' . $lastGetTotalVideos);
        include $global['systemRootPath'] . 'view/include/notfound.php';
    }
    ?>
</div>
<?php
restoreRequestVars();
?>