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

    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
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
    TimeLogEnd($timeLog, __LINE__);
    if ($obj->Categories) {
        $dataFlickirty = new stdClass();
        $dataFlickirty->wrapAround = true;
        $dataFlickirty->pageDots = !empty($obj->pageDots);
        $dataFlickirty->lazyLoad = true;
        $dataFlickirty->fade = true;
        $dataFlickirty->setGallerySize = false;
        $dataFlickirty->cellAlign = 'left';
        $dataFlickirty->groupCells = true;
        if ($obj->CategoriesAutoPlay) {
            $dataFlickirty->autoPlay = 10000;
            $dataFlickirty->wrapAround = true;
        } else {
            $dataFlickirty->wrapAround = true;
        }
        if (!empty($_GET['catName'])) {
            unset($_POST['sort']);
            $_POST['sort']['v.created'] = "DESC";
            $_POST['sort']['likes'] = "DESC";
            $_REQUEST['current'] = 1;
            $_REQUEST['rowCount'] = $obj->maxVideos;

            TimeLogStart("modeFlix.php::getAllVideos");
            $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
            TimeLogEnd("modeFlix.php::getAllVideos", __LINE__);
            TimeLogStart("modeFlix.php::getCategoryByName");
            $category = Category::getCategoryByName($_GET['catName']);
            TimeLogEnd("modeFlix.php::getCategoryByName", __LINE__);
            ?>
            <div class="row topicRow">
                <span class="md-col-12">&nbsp;</span>
                <h2>
                    <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $_GET['catName']; ?>"><i class="<?php echo $category['iconClass']; ?>"></i> <?php echo $category['name']; ?></a>
                </h2>
                <!-- Sub category -->
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                ?>
            </div>
            <?php
            TimeLogStart("modeFlix.php::while(1)");
            while (1) {
                $_REQUEST['current']++;
                $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
                if (empty($videos)) {
                    break;
                }
                echo '<div class="row topicRow">';
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                echo '</div>';
            }
            TimeLogEnd("modeFlix.php::while(1)", __LINE__);
            ?>
            <?php
            TimeLogStart("modeFlix.php::getChildCategoriesFromTitle");
            unset($_POST['sort']);
            $categoriesC = Category::getChildCategoriesFromTitle($_GET['catName']);
            foreach ($categoriesC as $value) {
                unset($_POST['sort']);
                $_GET['catName'] = $value['clean_name'];
                $_POST['sort']['v.created'] = "DESC";
                $_POST['sort']['likes'] = "DESC";
                $videos = Video::getAllVideos("viewableNotUnlisted", false, true);
                if (empty($videos)) {
                    continue;
                }
                ?>
                <div class="row topicRow">
                    <span class="md-col-12">&nbsp;</span>
                    <h2>
                        <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_name']; ?>"><i class="fas fa-folder"></i> <?php echo $value['name']; ?></a>
                    </h2>
                    <!-- Sub category -->
                    <?php
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/row.php';
                    ?>
                </div>
                <?php
                unset($_GET['catName']);
            }
            TimeLogEnd("modeFlix.php::getChildCategoriesFromTitle", __LINE__);
        } else {
            ?>
            <div id="categoriesContainer">
            </div>
            <p class="pagination">
                <a class="pagination__next" href="<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/modeFlixCategory.php?current=1&rrating=<?php echo @$_GET['rrating']; ?>&search=<?php echo @$_GET['search']; ?>"></a>
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
                        hideNav: '.pagination',
                        history: false,
                        checkLastPage: true
                    });
                    $container.on('request.infiniteScroll', function (event, path) {
                        //console.log('Loading page: ' + path);
                    });
                    $container.on('append.infiniteScroll', function (event, response, path, items) {
                        var id = "#" + items[0].id;
                        startModeFlix(id + " ");

                        $(id + " img.thumbsJPG").each(function (index) {
                            $(this).attr('src', $(this).attr('data-flickity-lazyload'));
                            $(this).addClass('flickity-lazyloaded');
                        });

                    });
                    $container.infiniteScroll('loadNextPage');
                    setTimeout(function () {
                        $container.infiniteScroll('loadNextPage');
                    }, 1000);
                });

            </script>
            <?php
        }
        ?>
        <script>
            $(document).ready(function () {
                setTimeout(function () {
                    $("img.thumbsJPG").each(function (index) {
                        $(this).attr('src', $(this).attr('data-flickity-lazyload'));
                        $(this).addClass('flickity-lazyloaded');
                    });
                }, 500);
            });
        </script>    
        <?php
    }
    TimeLogEnd($timeLog, __LINE__);
    unset($_POST['sort']);
    unset($_REQUEST['current']);
    unset($_REQUEST['rowCount']);
    ?>
</div>
