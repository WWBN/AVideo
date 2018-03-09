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

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        $_SESSION['type'] = "";
        unset($_SESSION['type']);
    }
}

require_once $global['systemRootPath'] . 'objects/video.php';


$video = Video::getVideo("", "viewableNotAd", false, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotAd");
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 24;
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';
$videos = Video::getAllVideos("viewableNotAd");
foreach ($videos as $key => $value) {
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
}
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __('Gallery'); ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="row text-center" style="padding: 10px;">
            <?php
            echo $config->getAdsense();
            ?>
        </div>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>
            <div class="col-xs-12 col-sm-10 col-md-10 col-lg-10 list-group-item">
                <?php
                if (!empty($videos)) {
                    $name = User::getNameIdentificationById($video['users_id']);
                    ?>
                    <div class="row mainArea">
                        <div class="clear clearfix firstRow">
                            <div class="row thumbsImage">
                                <div class="col-sm-6">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>/video/<?php echo $video['clean_title']; ?>" 
                                       title="<?php echo $video['title']; ?>" style="" >
                                           <?php
                                           $images = Video::getImageFromFilename($video['filename'], $video['type']);
                                           $imgGif = $images->thumbsGif;
                                           $poster = $images->poster;
                                           ?>                                        
                                        <div class="aspectRatio16_9">
                                            <img src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="thumbsJPG img img-responsive " style="height: auto; width: 100%;" id="thumbsJPG<?php echo $video['id']; ?>"  />

                                            <?php
                                            if (!empty($imgGif)) {
                                                ?>
                                                <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $video['title']; ?>" id="thumbsGIF<?php echo $video['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $video['rotation']; ?>" height="130" />
                                            <?php } ?>
                                        </div>
                                        <span class="duration"><?php echo Video::getCleanDuration($video['duration']); ?></span>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $video['clean_title']; ?>" title="<?php echo $video['title']; ?>">
                                        <h1><?php echo $video['title']; ?></h1>
                                    </a>
                                    <h4 itemprop="description"><?php echo nl2br(textToLink($video['description'])); ?></h4>

                                    <div class="text-muted galeryDetails">
                                        <div>
                                            <?php
                                            $value['tags'] = Video::getTags($video['id']);
                                            foreach ($value['tags'] as $value2) {
                                                if ($value2->label === __("Group")) {
                                                    ?>
                                                    <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div>
                                            <i class="fa fa-eye"></i>
                                            <span itemprop="interactionCount">
                                                <?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fa fa-clock-o"></i>
                                            <?php
                                            echo humanTiming(strtotime($video['videoCreation'])), " ", __('ago');
                                            ?>
                                        </div>
                                        <div class="userName">
                                            <i class="fa fa-user"></i>
                                            <?php
                                            echo $name;
                                            ?>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>

                        <div class="clear clearfix">
                            <h3 class="galleryTitle">
                                <i class="glyphicon glyphicon-sort-by-attributes"></i> <?php echo __("Date Added (newest)"); ?>
                            </h3>
                            <div class="row">
                                <?php
                                $countCols = 0;
                                foreach ($videos as $value) {
                                    $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                    $name = User::getNameIdentificationById($value['users_id']);
                                    // make a row each 6 cols
                                    if ($countCols % 6 === 0) {
                                        echo '</div><div class="row aligned-row ">';
                                    }
                                    $countCols++;
                                    ?>
                                    <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                        <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                            <?php
                                            $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                            $imgGif = $images->thumbsGif;
                                            $poster = $images->thumbsJpg;
                                            ?>
                                            <div class="aspectRatio16_9">
                                                <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>"  id="thumbsJPG<?php echo $value['id']; ?>"/>
                                                <?php
                                                if (!empty($imgGif)) {
                                                    ?>
                                                    <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
                                                <?php } ?>
                                            </div>
                                            <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                        </a>
                                        <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                            <h2><?php echo $value['title']; ?></h2>
                                        </a>  
                                        <div class="text-muted galeryDetails">
                                            <div>
                                                <?php
                                                $value['tags'] = Video::getTags($value['id']);
                                                foreach ($value['tags'] as $value2) {
                                                    if ($value2->label === __("Group")) {
                                                        ?>
                                                        <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div>
                                                <i class="fa fa-eye"></i>
                                                <span itemprop="interactionCount">
                                                    <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <i class="fa fa-clock-o"></i>
                                                <?php
                                                echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                ?>
                                            </div>
                                            <div class="userName">
                                                <i class="fa fa-user"></i>
                                                <?php
                                                echo $name;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <ul class="pages">
                        </ul>
                        <script>
                            $(document).ready(function () {
                                // Total Itens <?php echo $total; ?>

                                $('.pages').bootpag({
                                    total: <?php echo $totalPages; ?>,
                                    page: <?php echo $_GET['page']; ?>,
                                    maxVisible: 10
                                }).on('page', function (event, num) {
    <?php $url = '';
    if (strpos($_SERVER['REQUEST_URI'], "cat") === false) {
        $url = $global['webSiteRootURL'] . "page/";
    } else {
        $url = $global['webSiteRootURL'] . "cat/" . $video['clean_category'] . "/page/";
    } ?>
                                    window.location.replace("<?php echo $url; ?>" + num);
                                });
                            });
                        </script>
                    </div>


                    <div class="clear clearfix">
                        <h3 class="galleryTitle">
                            <i class="glyphicon glyphicon-eye-open"></i> <?php echo __("Most Watched"); ?>
                        </h3>
                        <div class="row">
                            <?php
                            $countCols = 0;
                            unset($_POST['sort']);
                            $_POST['sort']['views_count'] = "DESC";
                            $_POST['current'] = 1;
                            $_POST['rowCount'] = 12;
                            $videos = Video::getAllVideos();
                            foreach ($videos as $value) {
                                $name = User::getNameIdentificationById($value['users_id']);
                                // make a row each 6 cols
                                if ($countCols % 6 === 0) {
                                    echo '</div><div class="row aligned-row ">';
                                }
                                $countCols++;
                                ?>
                                <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
        <?php
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $imgGif = $images->thumbsGif;
        $poster = $images->thumbsJpg;
        ?>
                                        <div class="aspectRatio16_9">
                                            <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>" />

        <?php
        if (!empty($imgGif)) {
            ?>
                                                <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
        <?php } ?>
                                        </div>
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <h2><?php echo $value['title']; ?></h2>
                                    </a>

                                    <div class="text-muted galeryDetails">
                                        <div>
                                            <?php
                                            $value['tags'] = Video::getTags($value['id']);
                                            foreach ($value['tags'] as $value2) {
                                                if ($value2->label === __("Group")) {
                                                    ?>
                                                    <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                <?php
            }
        }
        ?>
                                        </div>
                                        <div>
                                            <i class="fa fa-eye"></i>
                                            <span itemprop="interactionCount">
                                            <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fa fa-clock-o"></i>
                                            <?php
                                            echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                            ?>
                                        </div>
                                        <div class="userName">
                                            <i class="fa fa-user"></i>
                                <?php
                                echo $name;
                                ?>
                                        </div>
                                    </div>
                                </div>
        <?php
    }
    ?>
                        </div>
                    </div>
                    <div class="clear clearfix">
                        <h3 class="galleryTitle">
                            <i class="glyphicon glyphicon-thumbs-up"></i> <?php echo __("Most Popular"); ?>
                        </h3>
                        <div class="row">
                            <?php
                            $countCols = 0;
                            unset($_POST['sort']);
                            $_POST['sort']['likes'] = "DESC";
                            $videos = Video::getAllVideos();
                            foreach ($videos as $value) {
                                $name = User::getNameIdentificationById($value['users_id']);
                                // make a row each 6 cols
                                if ($countCols % 6 === 0) {
                                    echo '</div><div class="row aligned-row ">';
                                }
                                $countCols++;
                                ?>
                                <div class="col-lg-2 col-md-4 col-sm-4 col-xs-6 galleryVideo thumbsImage fixPadding">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $value['clean_category']; ?>/video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
        <?php
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $imgGif = $images->thumbsGif;
        $poster = $images->thumbsJpg;
        ?>
                                        <div class="aspectRatio16_9">
                                            <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="thumbsJPG img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" id="thumbsJPG<?php echo $value['id']; ?>"/>

        <?php
        if (!empty($imgGif)) {
            ?>
                                                <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo $value['title']; ?>" id="thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" height="130" />
        <?php } ?>
                                        </div>
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <h2><?php echo $value['title']; ?></h2>
                                    </a>

                                    <div class="text-muted galeryDetails">
                                        <div>
                                            <?php
                                            $value['tags'] = Video::getTags($value['id']);
                                            foreach ($value['tags'] as $value2) {
                                                if ($value2->label === __("Group")) {
                                                    ?>
                                                    <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                <?php
            }
        }
        ?>
                                        </div>
                                        <div>
                                            <i class="fa fa-eye"></i>
                                            <span itemprop="interactionCount">
                                            <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fa fa-clock-o"></i>
                                            <?php
                                            echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                            ?>
                                        </div>
                                        <div class="userName">
                                            <i class="fa fa-user"></i>
                                <?php
                                echo $name;
                                ?>
                                        </div>
                                    </div>
                                </div>
                        <?php
                    }
                    ?>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="alert alert-warning">
                        <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
                    </div>
<?php } ?>
            </div>

            <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>


        </div>
<?php
include 'include/footer.php';
?>


    </body>
</html>
<?php
include $global['systemRootPath'] . 'objects/include_end.php';
?>