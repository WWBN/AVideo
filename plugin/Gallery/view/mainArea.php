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
        <!-- For Live Videos -->
        <div id="liveVideos" class="clear clearfix" style="display: none;">
            <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
            <div class="row extraVideos"></div>
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
        echo AVideoPlugin::getGallerySection();
        ?>
        <!-- For Live Videos End -->
        <?php
        if ($obj->Suggested) {
            createGallery(!empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested"), 'suggested', $obj->SuggestedRowCount, 'SuggestedOrder', "", "", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-star");
        }
        if ($obj->Trending) {
            createGallery(!empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending"), 'trending', $obj->TrendingRowCount, 'TrendingOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-chart-line");
        }
        if ($obj->SortByName) {
            createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString, "ASC", !$obj->hidePrivateVideos, "fas fa-font");
        }
        if ($obj->DateAdded) {
            createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-calendar-alt");
        }
        if ($obj->MostWatched) {
            createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC", !$obj->hidePrivateVideos, "far fa-eye");
        }
        if ($obj->MostPopular) {
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
                <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/view/modeGalleryCategory.php?current=1&search=<?php echo getSearchVar(); ?>"></a>
            </p>
            <div class="scroller-status">
                <div class="infinite-scroll-request loader-ellips text-center">
                    <i class="fas fa-spinner fa-pulse text-muted"></i>
                </div>
            </div>
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
                        console.log('Loading page: ' + path);
                    });
                    $container.on('append.infiniteScroll', function (event, response, path, items) {
                        console.log('Append page: ' + path);
                        lazyImage();
                    });
                    setTimeout(function () {
                        lazyImage();
                    }, 500);
                });

                function lazyImage() {
                    $('.thumbsJPG').lazy({
                        effect: 'fadeIn',
                        visibleOnly: true,
                        // called after an element was successfully handled
                        afterLoad: function (element) {
                            element.removeClass('blur');
                            element.parent().find('.thumbsGIF').lazy({
                                effect: 'fadeIn'
                            });
                        }
                    });
                    mouseEffect();
                }
            </script>
            <?php
        }
        ?>

        <?php
    } else {
        echo AVideoPlugin::getGallerySection();
        $contentSearchFound = true;
    }

    if (!$contentSearchFound) {
        ?>
        <div class="alert alert-warning">
            <span class="glyphicon glyphicon-facetime-video"></span>
            <strong><?php echo __("Warning"); ?>!</strong>
            <?php echo __("We have not found any videos or audios to show"); ?>.
        </div>
    <?php } ?>
</div>