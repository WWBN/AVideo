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
        ?>
        <center style="margin:5px;">
            <?php echo getAdsLeaderBoardTop2(); ?>
        </center>
        <?php
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
            <?php
        }else{
            ?>
            <script>
                function afterExtraVideos($liveLi) {
                    return false;
                }
            </script>
            <?php
        }
        echo AVideoPlugin::getGallerySection();
        ?>
        <!-- For Live Videos End -->
        <?php
        $countSections = 0;
        if ($obj->Suggested) {
            $countSections++;
            createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star");
        }
        if ($obj->Trending) {
            $countSections++;
            createGallery(!empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending"), 'trending', $obj->TrendingRowCount, 'TrendingOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-chart-line");
        }
        if ($obj->SortByName) {
            $countSections++;
            createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-font");
        }
        if ($obj->DateAdded && empty($_GET['catName'])) {
            $countSections++;
            createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-calendar-alt");
        }
        if ($obj->MostWatched) {
            $countSections++;
            createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-eye");
        }
        if ($obj->MostPopular) {
            $countSections++;
            createGallery(!empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular"), 'likes', $obj->MostPopularRowCount, 'mostPopularOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "fas fa-fire");
        }
        if ($obj->SubscribedChannels && User::isLogged() && empty($_GET['showOnly'])) {
            $channels = Subscribe::getSubscribedChannels(User::getId());
            foreach ($channels as $value) {
                $_POST['disableAddTo'] = 0;
                createChannelItem($value['users_id'], $value['photoURL'], $value['identification'], $obj->SubscribedChannelsRowCount);
            }
        }
        if ($obj->Categories && empty($_GET['catName']) && empty($_GET['showOnly'])) {
            ?>
            <div id="categoriesContainer"></div>
            <p class="pagination infiniteScrollPagination">
                <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/view/modeGalleryCategory.php?tags_id=<?php echo intval(@$_GET['tags_id']); ?>&current=1&search=<?php echo getSearchVar(); ?>"></a>
            </p>
            <div class="scroller-status">
                <div class="infinite-scroll-request loader-ellips text-center">
                    <i class="fas fa-spinner fa-pulse text-muted"></i>
                </div>
            </div>
            <script src="<?php echo $global['webSiteRootURL']; ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
            <script>
                $(document).ready(function () {
                    $container = $('#categoriesContainer').infiniteScroll({
                        path: '.pagination__next',
                        append: '.categoriesContainerItem',
                        status: '.scroller-status',
                        hideNav: '.infiniteScrollPagination',
                        prefill: true,
                        history: false
                    });
                    $container.on('request.infiniteScroll', function (event, path) {
                        //console.log('Loading page: ' + path);
                    });
                    $container.on('append.infiniteScroll', function (event, response, path, items) {
                        //console.log('Append page: ' + path);
                        lazyImage();
                    });
                    setTimeout(function () {
                        lazyImage();
                    }, 500);
                });
            </script>
            <?php
        }
        // if there is no section display only the dateAdded row for the selected category
        if (!empty($currentCat) && empty($_GET['showOnly'])) {
            if (empty($_GET['page'])) {
                $_GET['page'] = 1;
            }
            $_REQUEST['current'] = $_GET['page'];

            unset($_POST['sort']);
            $_POST['sort']['v.created'] = "DESC";
            $_POST['sort']['likes'] = "DESC";
            $_GET['catName'] = $currentCat['clean_name'];
            $_REQUEST['rowCount'] = $obj->CategoriesRowCount * 3;
            $videos = Video::getAllVideos("viewableNotUnlisted", false, !$obj->hidePrivateVideos);
            if (!empty($videos)) {
                ?>
                <div class="row clear clearfix" id="Div<?php echo $currentCat['clean_name']; ?>">
                    <h3 class="galleryTitle">
                        <a class="btn-default" href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $currentCat['clean_name']; ?>">
                            <i class="<?php echo $currentCat['iconClass']; ?>"></i> <?php echo $currentCat['name'] ; ?>
                        </a>
                    </h3>
                    <div class="Div<?php echo $currentCat['clean_name']; ?>Section">
                    <?php
                    createGallerySection($videos, "", array(), true);
                    ?>
                    </div>
                </div>
                <?php
                $total = Video::getTotalVideos("viewable");
                $totalPages = ceil($total / getRowCount());
                $page = $_GET['page'];
                if ($totalPages < $_GET['page']) {
                    $page = $totalPages;
                }
                ?>
                <div class="col-sm-12" style="z-index: 1;">
                    <?php
                    //getPagination($total, $page = 0, $link = "", $maxVisible = 10, $infinityScrollGetFromSelector="", $infinityScrollAppendIntoSelector="")
                    echo getPagination($totalPages, $page, "{$url}{page}{$args}", 10, ".Div{$currentCat['clean_name']}Section","#Div{$currentCat['clean_name']}");
                    ?>
                </div>
                <?php
            }
        }
        ?>

        <?php
    } else {
        $ob = ob_get_clean();
        ob_start();
        echo AVideoPlugin::getGallerySection();
        $ob2 = ob_get_clean();
        echo $ob;
        if (empty($ob2)) {
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