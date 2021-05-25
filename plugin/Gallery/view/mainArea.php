<div class="row mainArea">
    <?php
    if (!empty($currentCat)) {
        include $global['systemRootPath'] . 'plugin/Gallery/view/Category.php';
    }

    if ($obj->searchOnChannels && !empty($_GET['search'])) {
        $channels = User::getAllUsers(true);
        //cleanSearchVar();
        foreach ($channels as $value) {
            $contentSearchFound = true;
            createChannelItem($value['id'], $value['photoURL'], $value['identification']);
        }
        //reloadSearchVar();
    }
    if (!empty($video)) {
        $contentSearchFound = true;
        $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
        if (empty($_GET['search'])) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
        }
        echo '<center style="margin:5px;">' . getAdsLeaderBoardTop2() . '</center>';
        if (empty($_GET['catName'])) {
            ?>
            <!-- For Live Videos -->
            <div id="liveVideos" class="row clear clearfix" style="display: none;">
                <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
                <div class="extraVideos"></div>
            </div>
            <script>
                function afterExtraVideos($liveLi) {
                    $liveLi.removeClass('col-lg-12 col-sm-12 col-xs-12 bottom-border');
                    $liveLi.find('.thumbsImage').removeClass('col-lg-5 col-sm-5 col-xs-5');
                    $liveLi.find('.videosDetails').removeClass('col-lg-7 col-sm-7 col-xs-7');
                    $liveLi.addClass('col-lg-2 col-md-4 col-sm-4 col-xs-6 fixPadding');
                    $('#liveVideos').slideDown();
                    return $liveLi;
                }
            </script>
            <!-- For Live Videos End -->
            <?php
        } else {
            echo '<script>function afterExtraVideos($liveLi) {return false;}</script>';
        }
        echo AVideoPlugin::getGallerySection();

        $sections = Gallery::getSectionsOrder();
        $countSections = 0;
        if (!empty($_GET['catName'])) {
            $currentCat = Category::getCategoryByName($_GET['catName']);
            //createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);

            include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaCategory.php';
        } else {
            foreach ($sections as $value) {
                if (empty($value['active'])) {
                    continue;
                }
                $countSections++;
                if ($value['name'] == 'Suggested') {
                    createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star");
                } else
                if ($value['name'] == 'Trending') {
                    createGallery(!empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending"), 'trending', $obj->TrendingRowCount, 'TrendingOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-chart-line");
                } else
                if ($value['name'] == 'SortByName') {
                    createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-font");
                } else
                if ($value['name'] == 'DateAdded' && empty($_GET['catName'])) {
                    createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-calendar-alt");
                } else
                if ($value['name'] == 'MostWatched') {
                    createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-eye");
                } else
                if ($value['name'] == 'MostPopular') {
                    createGallery(!empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular"), 'likes', $obj->MostPopularRowCount, 'mostPopularOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "fas fa-fire");
                } else
                if ($value['name'] == 'SubscribedChannels' && User::isLogged() && empty($_GET['showOnly'])) {
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainAreaChannels.php';
                } else
                if ($value['name'] == 'Categories' && empty($_GET['showOnly'])) {
                    if(empty($currentCat) && !empty(getSearchVar())){
                        $onlySuggested = $obj->CategoriesShowOnlySuggested;
                        cleanSearchVar();
                        $categories = Category::getAllCategories(false, true, $onlySuggested);
                        reloadSearchVar(); 
                        foreach ($categories as $value) {
                            $currentCat = $value['clean_name'];
                            include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                        }
                    }else{
                        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategory.php';
                    }
                }
            }
            if (empty($countSections) && !empty($_GET['catName'])) {
                $category = Category::getCategoryByName($_GET['catName']);
                createGallery($category['name'], 'created', $obj->CategoriesRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, $category['iconClass'], true);
            }
        }
    } else {
        include $global['systemRootPath'] . 'plugin/Gallery/view/modeGalleryCategoryLive.php';
        $ob = ob_get_clean();
        ob_start();
        echo AVideoPlugin::getGallerySection();
        $ob2 = ob_get_clean();
        echo $ob;
        if (empty($contentSearchFound) && empty($ob2)) {
            $contentSearchFound = false;
        } else {
            $contentSearchFound = true;
        }
    }


    if (!$contentSearchFound) {
        _session_start();
        unset($_SESSION['type']);
        ?>
        <div class="alert alert-warning">
            <h1>
                <span class="glyphicon glyphicon-facetime-video"></span>
                <?php echo __("Warning"); ?>!
            </h1>
            <?php echo __("We have not found any videos or audios to show"); ?>.
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/notfound.php';
    }
    ?>
</div>