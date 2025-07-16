<?php
saveRequestVars();
?>
<link href="<?php echo getURL('plugin/Gallery/style.css'); ?>" rel="stylesheet" type="text/css" />
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
        $include = AVideoPlugin::getBigVideoIncludeFile();
        if (!empty($include)) {
            echo '<!-- BigVideoIncludeFile: ' . $include . ' -->';
            include $include;
        } else {
            if ($obj->showLivesAboveBigVideo) {
                include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaLiveRow.php';
            }
            include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideoLive.php';
        }
    }else{
        echo '<!-- BigVideoLive is not included because search is enabled -->';
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

        include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaLiveRow.php';

        echo AVideoPlugin::getGallerySection();

        $sections = Gallery::getSectionsOrder();
        if (!empty($_REQUEST['showOnly'])) {

            function sectionExists($sections, $name)
            {
                foreach ($sections as $section) {
                    if (!empty($section['name']) && $section['name'] === $name && !empty($section['active'])) {
                        echo '<!-- sectionExists ' . $name . ' -->';
                        return true;
                    }
                }
                echo '<!-- sectionExists ' . $name . ' not found -->';
                return false;
            }

            switch ($_REQUEST['showOnly']) {
                case 'TrendingOrder':
                    if (!sectionExists($sections, 'Trending')) {
                        $sections[] = array(
                            'name' => 'Trending',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'dateAddedOrder':
                    if (!sectionExists($sections, 'DateAdded')) {
                        $sections[] = array(
                            'name' => 'DateAdded',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'mostWatchedOrder':
                    if (!sectionExists($sections, 'MostWatched')) {
                        $sections[] = array(
                            'name' => 'MostWatched',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'mostPopularOrder':
                    if (!sectionExists($sections, 'MostPopular')) {
                        $sections[] = array(
                            'name' => 'MostPopular',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'privateContentOrder':
                    if (!sectionExists($sections, 'PrivateContent')) {
                        $sections[] = array(
                            'name' => 'PrivateContent',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'sortByNameOrder':
                    if (!sectionExists($sections, 'SortByName')) {
                        $sections[] = array(
                            'name' => 'SortByName',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;

                case 'suggestedOrder':
                    if (!sectionExists($sections, 'Suggested')) {
                        $sections[] = array(
                            'name' => 'Suggested',
                            'active' => true,
                            'order' => count($sections)
                        );
                    }
                    break;
            }
        }

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
                    echo '<!-- ' . basename(__FILE__) . ' Channel_' . $users_id . ' -->';
                    User::getChannelPanel($users_id);
                } else
                if ($value['name'] == 'Shorts' && empty($_GET['showOnly']) && AVideoPlugin::isEnabledByName('Shorts')) {
                    include $global['systemRootPath'] . 'plugin/Shorts/row.php';
                } else
                if ($value['name'] == 'Suggested') {
                    createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star", true);
                } else
                if (empty(getSearchVar()) && empty($_GET['showOnly']) && $value['name'] == 'PlayLists') {
                    $objPl = AVideoPlugin::getDataObject('PlayLists');
                    $plRows = PlayList::getAllToShowOnFirstPage();
                    //var_dump(count($plRows));exit;
                    if (!empty($plRows)) {
                        $rowCount = getRowCount();
                        setRowCount($obj->PlayListsRowCount);
                        foreach ($plRows as $pl) {
                            $videos = PlayList::getAllFromPlaylistsID($pl['id']);
                            if (empty($videos)) {
                                echo "<!-- there is no video for this playlist id = {$pl['id']} -->";
                                continue;
                            }
                            $playlistTotalInfo = PlayList::getTotalDurationAndTotalVideosFromPlaylist($pl['id']);
                ?>
                            <!-- For Playlist -->
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <a href="<?php echo "{$global['webSiteRootURL']}viewProgram/{$pl['id']}/" . urlencode($pl['name']); ?>" class="faa-parent animated-hover">
                                        <i class="fas fa-list"></i> <?php echo __($pl['name']); ?>
                                        <i class="fas fa-arrow-right faa-horizontal"></i>
                                        <span class="badge">
                                            (<?php echo $playlistTotalInfo['totalVideos']; ?> <?php echo __('videos'); ?>)
                                            <?php echo secondsToTime($playlistTotalInfo['duration_in_seconds'], '%02d'); ?>
                                        </span>
                                    </a>
                                </h3>
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
                } else
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
