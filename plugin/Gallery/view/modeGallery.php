<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';
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

if ($obj->sortReverseable) {
    if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
        $orderString = $_SERVER['REQUEST_URI'] . "&";
    } else {
        $orderString = $_SERVER['REQUEST_URI'] . "/?";
    }

    $orderString = str_replace("&&", "&", $orderString);
    $orderString = str_replace("//", "/", $orderString);
}

$video = Video::getVideo("", "viewableNotAd", false, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotAd");
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php
echo $config->getWebSiteTitle();
?></title>
        <meta name="generator"
              content="YouPHPTube - A Free Youtube Clone Script" />
              <?php include $global['systemRootPath'] . 'view/include/head.php';
              ?>
        <script>
            $(document).ready(function () {
                $('.pages').bootpag({
                    total: <?php echo $totalPages; ?>,
                    page: <?php echo $_GET['page']; ?>,
                    maxVisible: 10
                }).on('page', function (event, num) {
<?php
$url = '';
$args = '';

if (strpos($_SERVER['REQUEST_URI'], "?") != false) {
    $args = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], "?"), strlen($_SERVER['REQUEST_URI']));
}
echo 'var args = "' . $args . '";';

if (strpos($_SERVER['REQUEST_URI'], "/cat/") === false) {
    $url = $global['webSiteRootURL'] . "page/";
} else {
    $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
}
?>
                    window.location.replace("<?php echo $url; ?>" + num + args);
                });
            });
        </script>
    </head>

    <body>
        <?php include 'include/navbar.php'; ?>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="row text-center" style="padding: 10px;">
                <?php echo $config->getAdsense(); ?>
            </div>
            <div class="col-sm-10 col-sm-offset-1 list-group-item">
                <?php
		if (!empty($currentCat)) {
                        include $global['systemRootPath'] . 'plugin/Gallery/view/Category.php';
                    }
                if (!empty($video)) {
                    $name = User::getNameIdentificationById($video['users_id']);
                    $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
                    include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                    ?>

                    <div class="row mainArea">

                        <!-- For Live Videos -->
                        <div id="liveVideos" class="clear clearfix" style="display: none;">
                            <h3 class="galleryTitle text-danger"> <i class="fa fa-youtube-play"></i> <?php echo __("Live"); ?></h3>
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
                        <!-- For Live Videos End -->    
                        <?php if ($obj->SortByName) { ?>   
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-list-alt"></i>
                                    <?php
                                    if (empty($_GET["sortByNameOrder"])) {
                                        $_GET["sortByNameOrder"] = "ASC";
                                    }

                                    if ($obj->sortReverseable) {
                                        $info = createOrderInfo("sortByNameOrder", "zyx", "abc", $orderString);
                                        echo __("Sort by name (" . $info[2] . ")") . " (Page " . $_GET['page'] . ") <a href='" . $info[0] . "' >" . $info[1] . "</a>";
                                    } else {
                                        echo __("Sort by name (abc)");
                                    }
                                    ?>
                                </h3>
                                <?php
                                $countCols = 0;
                                unset($_POST['sort']);
                                $_POST['sort']['title'] = $_GET['sortByNameOrder'];
                                $_POST['current'] = $_GET['page'];
                                $_POST['rowCount'] = $obj->SortByNameRowCount;
                                $videos = Video::getAllVideos();
                                createGalerySection($videos);
                                ?>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } if ($obj->DateAdded) { ?> 
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-sort-by-attributes"></i>
                                    <?php
                                    if (empty($_GET["dateAddedOrder"])) {
                                        $_GET["dateAddedOrder"] = "DESC";
                                    }

                                    if ($obj->sortReverseable) {
                                        $info = createOrderInfo("dateAddedOrder", "newest", "oldest", $orderString);
                                        echo __("Date added (" . $info[2] . ")") . " (Page " . $_GET['page'] . ") <a href='" . $info[0] . "' >" . $info[1] . "</a>";
                                    } else {
                                        echo __("Date added (newest)");
                                    }
                                    ?>
                                </h3>
                                <div class="row"><?php
                            $countCols = 0;
                            unset($_POST['sort']);
                            $_POST['sort']['created'] = $_GET['dateAddedOrder'];
                            $_POST['rowCount'] = $obj->DateAddedRowCount;
                            $videos = Video::getAllVideos();
                            createGalerySection($videos);
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } if ($obj->MostWatched) { ?>
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-eye-open"></i>
                                    <?php
                                    if (empty($_GET['mostWatchedOrder'])) {
                                        $_GET['mostWatchedOrder'] = "DESC";
                                    }
                                    if ($obj->sortReverseable) {
                                        $info = createOrderInfo("mostWatchedOrder", "Most", "Lessest", $orderString);
                                        echo __($info[2] . " watched") . " (Page " . $_GET['page'] . ") <a href='" . $info[0] . "' >" . $info[1] . "</a>";
                                    } else {
                                        echo __("Most watched");
                                    }
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['views_count'] = $_GET['mostWatchedOrder'];
                                    $_POST['current'] = $_GET['page'];
                                    $_POST['rowCount'] = $obj->MostWatchedRowCount;
                                    $videos = Video::getAllVideos();
                                    createGalerySection($videos);
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } if ($obj->MostPopular) { ?>    
                            <div class="clear clearfix">
                                <h3 class="galleryTitle">
                                    <i class="glyphicon glyphicon-thumbs-up"></i>
                                    <?php
                                    if (empty($_GET['mostPopularOrder'])) {
                                        $_GET['mostPopularOrder'] = "DESC";
                                    }
                                    if ($obj->sortReverseable) {
                                        $info = createOrderInfo("mostPopularOrder", "Most", "Lessest", $orderString);
                                        echo __($info[2] . " popular") . " (Page " . $_GET['page'] . ") <a href='" . $info[0] . "' >" . $info[1] . "</a>";
                                    } else {
                                        echo __("Most popular");
                                    }
                                    ?>
                                </h3>
                                <div class="row">
                                    <?php
                                    $countCols = 0;
                                    unset($_POST['sort']);
                                    $_POST['sort']['likes'] = $_GET['mostPopularOrder'];
                                    $_POST['current'] = $_GET['page'];
                                    $_POST['rowCount'] = $obj->MostPopularRowCount;
                                    $videos = Video::getAllVideos();
                                    createGalerySection($videos);
                                    ?>
                                </div>
                                <div class="row">
                                    <ul class="pages">
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-warning">
                        <span class="glyphicon glyphicon-facetime-video"></span>
                        <strong><?php echo __("Warning"); ?>!</strong> 
                        <?php echo __("We have not found any videos or audios to show"); ?>.
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php include 'include/footer.php'; ?>
</body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
