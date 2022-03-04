<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
$leaderBoardTop = getAdsLeaderBoardTop();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php
echo $siteTitle;
?></title>
            <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid gallery">
            <?php
            if (!empty($leaderBoardTop)) {
                echo '<div class="row text-center" style="padding: 10px;">' . $leaderBoardTop . '</div>';
            } else {
                echo '<!-- getAdsLeaderBoardTop is empty -->';
            }
            ?>
            <div class="col-lg-10 col-lg-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row mainArea">
                            <!-- For Live Videos -->
                            <div id="liveVideos" class="clear clearfix" style="display: none;">
                                <h3 class="galleryTitle text-danger"> <i class="fas fa-play-circle"></i> <?php echo __("Live"); ?></h3>
                                <div class="extraVideos"></div>
                            </div>
                            <!-- For Live Schedule Videos -->
                            <div id="liveScheduleVideos" class="clear clearfix" style="display: none;">
                                <h3 class="galleryTitle"> <i class="far fa-calendar-alt"></i> <?php echo __($objLive->live_schedule_label); ?></h3>
                                <div class="extraVideos"></div>
                            </div>
                            <!-- For Live Videos End -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/Gallery/view/footer.php';
        ?>
    </body>
</html>
<?php include_once $global['systemRootPath'] . 'objects/include_end.php'; ?>


