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

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 10;
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';
$videos = Video::getAllVideos("viewableNotAd");
foreach ($videos as $key => $value) {
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
}
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __('Gallery'); ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - Make your own tube sitetags " />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">

            <?php
            if (!empty($videos)) {
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-lg-1"></div>
                        <div class="col-xs-12 col-sm-12 col-lg-10">
                            <?php
                            foreach ($videos as $value) {
                                ?>
                                <div class="col-lg-3 col-sm-6 col-xs-12">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <img src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $value['filename']; ?>.jpg" alt="<?php echo $value['title']; ?>" class="img img-responsive img-rounded img-thumbnail" height="130px" />
                                        <h2><?php echo $value['title']; ?></h2>
                                        <span class="glyphicon glyphicon-play-circle"></span>
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                </div>
                                <?php
                            }
                            ?> 
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
                                        window.location.replace("<?php echo $global['webSiteRootURL']; ?>page/" + num);
                                    });
                                });
                            </script>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-lg-1"></div>
                    </div>
                    <?php
            } else {
                ?>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
                </div>
            <?php } ?>  

        </div>
        <?php
        include 'include/footer.php';
        ?>


    </body>
</html>
