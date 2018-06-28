<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

$obj = YouPHPTubePlugin::getObjectData("Gallery");
if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        unset($_SESSION['type']);
    }
}
require_once $global['systemRootPath'] . 'objects/category.php';
$currentCat;
$currentCatType;
if (!empty($_GET['catName'])) {
    $currentCat = Category::getCategoryByName($_GET['catName']);
    $currentCatType = Category::getCategoryType($currentCat['id']);
}
if ((empty($_GET['type'])) && (!empty($currentCatType))) {
    if ($currentCatType['type'] == "1") {
        $_SESSION['type'] = "audio";
    } else if ($currentCatType['type'] == "2") {
        $_SESSION['type'] = "video";
    } else {
        unset($_SESSION['type']);
    }
}
require_once $global['systemRootPath'] . 'objects/video.php';
$orderString = "";
if ($obj->sortReverseable) {
    if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
        $orderString = $_SERVER['REQUEST_URI'] . "&";
    } else {
        $orderString = $_SERVER['REQUEST_URI'] . "/?";
    }
    $orderString = str_replace("&&", "&", $orderString);
    $orderString = str_replace("//", "/", $orderString);
}
$video = Video::getVideo("", "viewable", false, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewable");
}
if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$total = 0;
$totalPages = 0;
$url = '';
$args = '';
if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
    $args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?"), strlen($_SERVER['REQUEST_URI']));
}
if (strpos($_SERVER['REQUEST_URI'], "/cat/") === false) {
    $url = $global['webSiteRootURL'] . "page/";
} else {
    $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
}
$contentSearchFound = false;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php
            echo $config->getWebSiteTitle();
            ?></title>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
    </head>

    <body>
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="row text-center" style="padding: 10px;">
                <?php echo $config->getAdsense(); ?>
            </div>
            <div class="col-sm-10 col-sm-offset-1 list-group-item">

                <div class="row mainArea">
                    <?php
                    if (!empty($currentCat)) {
                        include $global['systemRootPath'] . 'plugin/Gallery/view/Category.php';
                    }

                    if ($obj->searchOnChannels && !empty($_GET['search'])) {
                        $channels = User::getAllUsers();
                        clearSearch();
                        foreach ($channels as $value) {
                            $contentSearchFound = true;
                            createChannelItem($value['id'], $value['photoURL'], $value['identification']);
                        }
                        reloadSearch();
                    }

                    if (!empty($video)) {
                        $contentSearchFound = true;
                        $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
                        include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                        ?>

                        <!-- For Live Videos -->
                        <div id="liveVideos" class="clear clearfix" style="display: none;">
                            <h3 class="galleryTitle text-danger"> <i class="fab fa-youtube"></i> <?php echo __("Live"); ?></h3>
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
                        echo YouPHPTubePlugin::getGallerySection();
                        ?>
                        <!-- For Live Videos End -->
                        <?php
                        if ($obj->SortByName) {
                            createGallery(!empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name"), 'title', $obj->SortByNameRowCount, 'sortByNameOrder', "zyx", "abc", $orderString);
                        }
                        if ($obj->DateAdded) {
                            createGallery(!empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added"), 'created', $obj->DateAddedRowCount, 'dateAddedOrder', __("newest"), __("oldest"), $orderString, "DESC");
                        }
                        if ($obj->MostWatched) {
                            createGallery(!empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched"), 'views_count', $obj->MostWatchedRowCount, 'mostWatchedOrder', __("Most"), __("Fewest"), $orderString, "DESC");
                        }
                        if ($obj->MostPopular) {
                            createGallery(!empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular"), 'likes', $obj->MostPopularRowCount, 'mostPopularOrder', __("Most"), __("Fewest"), $orderString, "DESC");
                        }
                        if ($obj->SubscribedChannels && User::isLogged() && empty($_GET['showOnly'])) {
                            $channels = Subscribe::getSubscribedChannels(User::getId());
                            foreach ($channels as $value) {
                                createChannelItem($value['users_id'], $value['photoURL'], $value['identification'], $obj->SubscribedChannelsRowCount);
                            }
                        }
                        ?>
                    <?php }

                    if (!$contentSearchFound) {
                        ?>
                        <div class="alert alert-warning">
                            <span class="glyphicon glyphicon-facetime-video"></span>
                            <strong><?php echo __("Warning"); ?>!</strong>
                        <?php echo __("We have not found any videos or audios to show"); ?>.
                        </div>
<?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php include $global['systemRootPath'] . 'view/include/footer.php'; ?>
</body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
