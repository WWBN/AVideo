<?php
include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideo.php';
$percent = 90;
?>
<div id="carouselRows" style="
     background-color: rgb(<?php echo $obj->backgroundRGB; ?>);
     background: -webkit-linear-gradient(bottom, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
     background: -o-linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
     background: linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
     background: -moz-linear-gradient(to top, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
     ">
         <?php
         $_REQUEST['current'] = 1;
         $_REQUEST['rowCount'] = $obj->maxVideos;

         if ($obj->Suggested) {
             $dataFlickirty = new stdClass();
             $dataFlickirty->wrapAround = true;
             $dataFlickirty->pageDots = !empty($obj->pageDots);
             $dataFlickirty->lazyLoad = 15;
             $dataFlickirty->setGallerySize = false;
             $dataFlickirty->cellAlign = 'left';
             $dataFlickirty->groupCells = true;
             if ($obj->SuggestedAutoPlay) {
                 $dataFlickirty->autoPlay = 10000;
             }

             //getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false)
             $videos = Video::getAllVideos("viewableNotUnlisted", false, true, array(), false, false, true, true);
             if (!empty($videos)) {
                 ?>
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                    echo __("Suggested");
                    ?>
                </h2>
                <!-- Date Added -->
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                ?>
            </div>

            <?php
        }
    }

    if ($obj->Channels) {
        require_once $global['systemRootPath'] . 'objects/Channel.php';
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->ChannelsAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
        }
        $users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();
        $channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");
        if (!empty($channels)) {
            foreach ($channels as $channel) {
                $_POST['sort']['created'] = "DESC";
                $videos = Video::getAllVideosAsync("viewable", $channel['id']);
                unset($_POST['sort']['created']);
                $link = User::getChannelLinkFromChannelName($channel["channelName"]);
                ?>
                <div class="row topicRow">
                    <h2>
                        <a href="<?php echo $link; ?>">
                            <img src="<?php echo $global['webSiteRootURL'] . $channel["photoURL"]; ?>" class="img img-responsive pull-left" style="max-width: 18px; max-height: 18px; margin-right: 5px;"> <?php
                            echo $channel["channelName"];
                            ?>
                        </a>
                    </h2>
                    <!-- Date Programs/Playlists -->
                    <?php
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                    ?>
                </div>

                <?php
            }
        }
    }


    $plObj = AVideoPlugin::getDataObjectIfEnabled('PlayLists');
    if ($obj->PlayList && !empty($plObj)) {
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->PlayListAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
        }

        $programs = Video::getAllVideos("viewableNotUnlisted", false, true, array(), false, false, true, false, true);
        cleanSearchVar();
        if (!empty($programs)) {
            foreach ($programs as $serie) {
                $videos = PlayList::getAllFromPlaylistsID($serie['serie_playlists_id']);

                foreach ($videos as $key => $value) {
                    $videos[$key]['title'] = "{$value['icon']} {$value['title']}";
                }

                $link = PlayLists::getLink($serie['serie_playlists_id']);
                $linkEmbed = PlayLists::getLink($serie['serie_playlists_id'], true);
                $canWatchPlayButton = "";
                if (User::canWatchVideoWithAds($value['id'])) {
                    $canWatchPlayButton = "canWatchPlayButton";
                }
                ?>
                <div class="row topicRow">
                    <h2>
                        <a href="<?php echo $link; ?>" embed="<?php echo $linkEmbed; ?>" class="<?php echo $canWatchPlayButton; ?>">
                            <i class="fas fa-list"></i> <?php
                            echo $serie['title'];
                            ?>
                        </a>
                    </h2>
                    <!-- Date Programs/Playlists -->
                    <?php
                    $rowPlayListLink = PlayLists::getLink($serie['serie_playlists_id']);
                    $rowPlayListLinkEmbed = PlayLists::getLink($serie['serie_playlists_id'], true);
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                    unset($rowPlayListLink);
                    unset($rowPlayListLinkEmbed);
                    ?>
                </div>

                <?php
            }
        }
        reloadSearchVar();
    }

    if ($obj->Trending) {
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->TrendingAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
        }

        $_POST['sort']['trending'] = "";

        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        unset($_POST['sort']['trending']);
        if (!empty($videos)) {
            ?>
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                    echo __("Trending");
                    ?>
                </h2>
                <!-- Date Added -->
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                ?>
            </div>

            <?php
        }
    }
    if ($obj->DateAdded) {
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->DateAddedAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
        }

        unset($_POST['sort']);
        $_POST['sort']['created'] = "DESC";

        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        if (!empty($videos)) {
            ?>
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                    echo __("Date added (newest)");
                    ?>
                </h2>
                <!-- Date Added -->
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                ?>
            </div>

            <?php
        }
    }
    if ($obj->MostPopular) {
        $_REQUEST['rowCount'] = $obj->maxVideos;
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->MostPopularAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
            $dataFlickirty->wrapAround = true;
        } else {
            $dataFlickirty->wrapAround = true;
        }
        unset($_POST['sort']);
        $_POST['sort']['likes'] = "DESC";
        $_POST['sort']['v.created'] = "DESC";
        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        ?>
        <div class="row topicRow">
            <span class="md-col-12">&nbsp;</span>
            <h2>
                <i class="glyphicon glyphicon-thumbs-up"></i> <?php echo __("Most popular"); ?>
            </h2>
            <!-- Most Popular -->
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
            ?>
        </div>


        <?php
    }
    if ($obj->MostWatched) {
        $_REQUEST['rowCount'] = $obj->maxVideos;
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->MostWatchedAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
            $dataFlickirty->wrapAround = true;
        } else {
            $dataFlickirty->wrapAround = true;
        }
        unset($_POST['sort']);
        $_POST['sort']['views_count'] = "DESC";
        $_POST['sort']['created'] = "DESC";
        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        ?>
        <span class="md-col-12">&nbsp;</span>
        <div class="row topicRow">
            <h2>
                <i class="glyphicon glyphicon-eye-open"></i> <?php echo __("Most watched"); ?>
            </h2>
            <!-- Most watched -->
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
            ?>
        </div>
        <?php
    }
    if ($obj->SortByName) {
        $_REQUEST['rowCount'] = $obj->maxVideos;
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = 15;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->SortByNameAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
            $dataFlickirty->wrapAround = true;
        } else {
            $dataFlickirty->wrapAround = true;
        }
        unset($_POST['sort']);
        $_POST['sort']['title'] = "ASC";
        $_POST['sort']['created'] = "DESC";
        $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
        ?>
        <span class="md-col-12">&nbsp;</span>
        <div class="row topicRow">
            <h2>
                <i class="fas fa-sort-alpha-down"></i> <?php echo __("Alphabetical"); ?>
            </h2>
            <!-- Most watched -->
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
            ?>
        </div>
        <?php
    }
    if ($obj->Categories) {
        $url = "{$global['webSiteRootURL']}plugin/YouPHPFlix2/view/modeFlixCategory.php";
        if (!empty($_GET['catName'])) {
            $url = addQueryStringParameter($url, 'catName', $_GET['catName']);
        }
        $search = getSearchVar();
        if (!empty($search)) {
            $url = addQueryStringParameter($url, 'search', $search);
        }
        $url = addQueryStringParameter($url, 'tags_id', intval(@$_GET['tags_id']));
        $url = addQueryStringParameter($url, 'current', 1);
        ?>
        <div id="categoriesContainer"></div>
        <p class="pagination infiniteScrollPagination">
            <a class="pagination__next" href="<?php echo $url; ?>"></a>
        </p>
        <div class="scroller-status">
            <div class="infinite-scroll-request loader-ellips text-center">
                <i class="fas fa-spinner fa-pulse text-muted"></i>
            </div>
        </div>
        <script src="<?php echo getCDN(); ?>view/js/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
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

                    $("img.thumbsJPG").not('flickity-lazyloaded').each(function (index) {
                        $(this).attr('src', $(this).attr('data-flickity-lazyload'));
                        $(this).addClass('flickity-lazyloaded');
                    });

                    lazyImage();
                    if (typeof transformLinksToEmbed === 'function') {
                        transformLinksToEmbed('a.galleryLink');
                        transformLinksToEmbed('a.canWatchPlayButton');
                    }
                });
                setTimeout(function () {
                    lazyImage();
                    if (typeof transformLinksToEmbed === 'function') {
                        transformLinksToEmbed('a.galleryLink');
                    }
                }, 500);
            });
        </script>
        <?php
    }
    unset($_POST['sort']);
    unset($_REQUEST['current']);
    unset($_REQUEST['rowCount']);
    ?>
</div>
