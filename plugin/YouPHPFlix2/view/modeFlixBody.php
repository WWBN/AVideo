<?php
include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideo.php';
$percent = 90;
$styleBG =  "
background-color: rgb({$obj->backgroundRGB});
background: -webkit-linear-gradient(bottom, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
background: -o-linear-gradient(top, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
background: linear-gradient(top, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
background: -moz-linear-gradient(to top, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
";
$videoFound = false;
?>
<div id="carouselRows" style="<?php echo $styleBG; ?>">
    <?php
    unsetCurrentPage();
    $_REQUEST['rowCount'] = $obj->maxVideos;

    if (User::isLogged()) {
        $search = getSearchVar();
        if (empty($search)) {
            $plObj = AVideoPlugin::getDataObjectIfEnabled('PlayLists');
            if (!empty($plObj)) {
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
                if ($obj->WatchLater) {
                    $playlists_id = PlayList::getWatchLaterIdFromUser(User::getId());
                    $rowCount = getRowCount();
                    $videos = PlayList::getAllFromPlaylistsID($playlists_id);
                    if (!empty($videos)) {
                        $videoFound = true;
                        $link = PlayLists::getLink($playlists_id);
                        $linkEmbed = PlayLists::getLink($playlists_id, true);
                        ?>
                        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
                        <div class="row topicRow">
                            <h2>
                                <a href="<?php echo $link; ?>" embed="<?php echo $linkEmbed; ?>">
                                    <!-- modeFlixBody line <?php echo __LINE__; ?> -->
                                    <i class="fas fa-clock"></i> <?php echo __('Watch Later'); ?>
                                </a>
                            </h2>
                            <!-- Date Programs/Playlists 2 -->
                            <?php
                            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                            ?>
                        </div>
                    <?php
                    }
                }
                if ($obj->Favorites) {
                    $playlists_id = PlayList::getFavoriteIdFromUser(User::getId());
                    $rowCount = getRowCount();
                    $videos = PlayList::getAllFromPlaylistsID($playlists_id);
                    if (!empty($videos)) {
                        $videoFound = true;
                        $link = PlayLists::getLink($playlists_id);
                        $linkEmbed = PlayLists::getLink($playlists_id, true);
                        ?>
                        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
                        <div class="row topicRow">
                            <h2>
                                <a href="<?php echo $link; ?>" embed="<?php echo $linkEmbed; ?>">
                                    <!-- modeFlixBody line <?php echo __LINE__; ?> -->
                                    <i class="fas fa-heart"></i> <?php echo __('Favorites'); ?>
                                </a>
                            </h2>
                            <!-- Date Programs/Playlists 2 -->
                            <?php
                            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                            ?>
                        </div>
                        <?php
                    }
                }
            }
        }
    }

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

        //getAllVideos($status = Video::SORT_TYPE_VIEWABLE, $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true, $suggestedOnly = false)
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos, array(), false, false, true, true);
        if (!empty($videos)) {
            $videoFound = true;
            ?>
            <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                                                                            echo __($obj->SuggestedCustomTitle);
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
    $channels = $users_id_array = array();
    require_once $global['systemRootPath'] . 'objects/Channel.php';
    if ($obj->Channels) {
        $users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();
        $channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");
    }

    foreach ($obj as $key => $value) {
        if ($value === true && preg_match('/Channel_([0-9]+)_$/', $key, $matches)) {
            $users_id = intval($matches[1]);
            if (in_array($users_id, $users_id_array)) {
                continue;
            }
            $users_id_array[] = $users_id;
        }
    }
    if (!empty($users_id_array)) {
        $channels2 = Channel::getChannels(true, '', $users_id_array);
        $channels = array_merge($channels, $channels2);
    }
    if (!empty($channels)) {
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
        $countChannels = 0;
        foreach ($channels as $channel) {
            if ($countChannels > 5) {
                break;
            }
            $_POST['sort']['created'] = "DESC";
            $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, $channel['id']);
            if(!empty($videos)){
                $videoFound = true;
            }
            unset($_POST['sort']['created']);
            if (empty($videos)) {
                continue;
            }
            $countChannels++;
            $link = User::getChannelLinkFromChannelName($channel["channelName"]);
        ?>
            <div class="row topicRow channelRow channel_<?php echo $channel["id"]; ?>">
                <h2>
                    <a href="<?php echo $link; ?>">
                        <img src="<?php echo User::getPhoto($channel["id"]); ?>" class="img img-responsive pull-left" style="max-width: 18px; max-height: 18px; margin-right: 5px;">
                        <?php echo $channel["channelName"]; ?>
                    </a>
                </h2>
                <!-- Date Programs/Playlists 1 -->
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                ?>
            </div>

            <?php
        }
    }


    $search = getSearchVar();
    if (empty($search)) {
        $plObj = AVideoPlugin::getDataObjectIfEnabled('PlayLists');
        if (!empty($plObj)) {

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
            $plRows = PlayList::getAllToShowOnFirstPage();
            //var_dump(count($plRows));exit;
            if (!empty($plRows)) {
                $rowCount = getRowCount();
                foreach ($plRows as $pl) {
                    $videos = PlayList::getAllFromPlaylistsID($pl['id']);
                    if(!empty($videos)){
                        $videoFound = true;
                    }
                    if (empty($videos)) {
                        continue;
                    }
                    $link = PlayLists::getLink($pl['id']);
                    $linkEmbed = PlayLists::getLink($pl['id'], true);

                    $videoSerie = Video::getVideoFromSeriePlayListsId($pl['id']);
            ?>
                    <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
                    <div class="row topicRow">
                        <h2>
                            <a href="<?php echo $link; ?>" embed="<?php echo $linkEmbed; ?>">
                                <!-- modeFlixBody line <?php echo __LINE__; ?> -->
                                <i class="fas fa-list"></i> <?php echo __($pl['name']); ?>
                            </a>
                            <?php
                            if (!empty($videoSerie)) {
                            ?>
                                <span style="margin-left: 10px;">
                                    <?php
                                    echo Video::generatePlaylistButtons($videoSerie['id'], 'btn btn-dark btn-xs', 'background-color: #11111199; ', false);
                                    ?>
                                </span>
                            <?php
                            } else {
                                echo '<!-- Playlists_id [' . $pl['id'] . '] is not a serie -->';
                            }
                            ?>
                        </h2>
                        <!-- Date Programs/Playlists 2 -->
                        <?php
                        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                        ?>
                    </div>
                    <?php
                }
            }
            if ($obj->PlayList) {
                $programs = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos, array(), false, false, true, false, true);
                cleanSearchVar();
                if (!empty($programs)) {
                    foreach ($programs as $serie) {
                        $videos = PlayList::getAllFromPlaylistsID($serie['serie_playlists_id']);

                        if(!empty($videos)){
                            $videoFound = true;
                        }
                        foreach ($videos as $key => $value) {
                            $videos[$key]['title'] = "{$value['icon']} {$value['title']}";
                        }

                        if (empty($videos)) {
                            continue;
                        }
                        $link = PlayLists::getLink($serie['serie_playlists_id']);
                        $linkEmbed = PlayLists::getLink($serie['serie_playlists_id'], true);
                        $canWatchPlayButton = "";
                        if (User::canWatchVideoWithAds($value['id'])) {
                            $canWatchPlayButton = "canWatchPlayButton";
                        } else if ($obj->hidePlayButtonIfCannotWatch) {
                            $canWatchPlayButton = "hidden";
                        }
                    ?>
                        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
                        <div class="row topicRow">
                            <h2 class="pull-left">
                                <a href="<?php echo $link; ?>" embed="<?php echo $linkEmbed; ?>" class="<?php echo $canWatchPlayButton; ?>">
                                    <!-- modeFlixBody line <?php echo __LINE__; ?> -->
                                    <i class="fas fa-list"></i> <?php echo $serie['title']; ?>
                                </a>
                            </h2>
                            <span style="margin-left: 5px;">
                                <?php
                                echo Video::generatePlaylistButtons($serie['id'], 'btn btn-dark', 'background-color: #11111199;', false);
                                ?>
                            </span>
                            <!-- Date Programs/Playlists 3 -->
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
        }
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

        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        unset($_POST['sort']['trending']);
        if (!empty($videos)) {
            $videoFound = true;
            ?>
            <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                                                                            echo __($obj->TrendingCustomTitle);
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

        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        if (!empty($videos)) {
            $videoFound = true;
        ?>
            <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
            <div class="row topicRow">
                <h2>
                    <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php
                                                                            echo __($obj->DateAddedCustomTitle);
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
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        if(!empty($videos)){
            $videoFound = true;
        }
        ?>
        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
        <div class="row topicRow">
            <span class="md-col-12">&nbsp;</span>
            <h2>
                <i class="glyphicon glyphicon-thumbs-up"></i> <?php echo __($obj->MostPopularCustomTitle); ?>
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
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        if(!empty($videos)){
            $videoFound = true;
        }
    ?>
        <span class="md-col-12">&nbsp;</span>
        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
        <div class="row topicRow">
            <h2>
                <i class="glyphicon glyphicon-eye-open"></i> <?php echo __($obj->MostWatchedCustomTitle); ?>
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
        $videos = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos);
        if(!empty($videos)){
            $videoFound = true;
        }
    ?>
        <span class="md-col-12">&nbsp;</span>
        <!-- modeFlixBody line=<?php echo __LINE__; ?> -->
        <div class="row topicRow">
            <h2>
                <i class="fas fa-sort-alpha-down"></i> <?php echo __($obj->SortByNameCustomTitle); ?>
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
        if (!empty($_REQUEST['catName'])) {
            $url = addQueryStringParameter($url, 'catName', $_REQUEST['catName']);
        }
        $search = getSearchVar();
        if (!empty($search)) {
            $url = addQueryStringParameter($url, 'search', $search);
        }
        $url = addQueryStringParameter($url, 'tags_id', intval(@$_GET['tags_id']));
        $url = addQueryStringParameter($url, 'current', 1);
        if (!empty($_REQUEST['search'])) {
            $url = addQueryStringParameter($url, 'search', $_REQUEST['search']);
        }
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
        <script src="<?php echo getCDN(); ?>node_modules/infinite-scroll/dist/infinite-scroll.pkgd.min.js" type="text/javascript"></script>
        <script>
            $(document).ready(function() {
                $container = $('#categoriesContainer').infiniteScroll({
                    path: '.pagination__next',
                    append: '.categoriesContainerItem',
                    status: '.scroller-status',
                    hideNav: '.infiniteScrollPagination',
                    prefill: true,
                    history: false
                });
                $container.on('request.infiniteScroll', function(event, path) {
                    //console.log('Loading page: ' + path);
                });
                $container.on('append.infiniteScroll', function(event, response, path, items) {
                    //console.log('Append page: ' + path);

                    $("img.thumbsJPG").not('flickity-lazyloaded').each(function(index) {
                        $(this).attr('src', $(this).attr('data-flickity-lazyload'));
                        $(this).addClass('flickity-lazyloaded');
                    });

                    lazyImage();
                    if (typeof transformLinksToEmbed === 'function') {
                        transformLinksToEmbed('a.galleryLink');
                        transformLinksToEmbed('a.canWatchPlayButton');
                    }
                });
                setTimeout(function() {
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
    resetCurrentPage();
    ?>
</div>
<?php
if(!$videoFound){
    include_once __DIR__.'/notFoundHTML.php';
}
?>
