<?php
global $advancedCustom;
$uid = uniqid();
$obj2 = AVideoPlugin::getObjectData("YouPHPFlix2");

if (!empty($global['isChannel'])) {
    $video = Video::getVideo('', Video::SORT_TYPE_CHANNELSUGGESTED, !$obj2->hidePrivateVideos, true);
}
if (empty($video)) {
    $video = Video::getVideo("", Video::SORT_TYPE_VIEWABLENOTUNLISTED, !$obj2->hidePrivateVideos, false, true);
}
if (empty($video)) {
    $video = Video::getVideo("", Video::SORT_TYPE_VIEWABLENOTUNLISTED, !$obj2->hidePrivateVideos, true);
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    if (empty($video)) {
        include_once __DIR__.'/notFoundHTML.php';
    } else {
        $name = User::getNameIdentificationById($video['users_id']);
        $images = Video::getImageFromFilename($video['filename'], $video['type']);
        $imgGif = $images->thumbsGif;
        $poster = $images->poster;
    ?>
        <div style="padding-bottom: 40%;"></div>
        <div class="embed-responsive-16by9" id="bigVideo" style="background-color: rgb(<?php echo $obj->backgroundRGB; ?>);
             background: url(<?php echo $poster; ?>);
             -webkit-background-size: cover;
             -moz-background-size: cover;
             -o-background-size: cover;
             background-size: cover;
             z-index: 0;
             position: absolute;
             top: 0;
             width: 100%;">
            <?php
            if (!isMobile() && !empty($video['trailer1'])) {
                $percent = 2;
            ?>
                <div id="bg_container" class="" style="height: 100%;">
                    <iframe src="<?php echo parseVideos($video['trailer1'], 1, 1, 1, 0, 0, 0, 'cover'); ?>" frameborder="0" allowtransparency="true" allow="autoplay"></iframe>
                </div>
                <div id="bg_container_overlay"></div>
            <?php
            } else {
                $percent = 40;
            }
            $style = "
            padding: 60px 20px 56.25% 20px;
            background: -webkit-linear-gradient(left, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
            background: -o-linear-gradient(right, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
            background: linear-gradient(right, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
            background: -moz-linear-gradient(to right, rgba({$obj->backgroundRGB},1) {$percent}%, rgba({$obj->backgroundRGB},0) 100%);
            ";
            ?>

            <div class="posterDetails" style="<?php echo $style; ?>">
                <?php
                include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideoInfoDetails.php';
                ?>
                <div class="row hidden-xs">
                    <?php
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideoPosterDescription.php';
                    ?>
                </div>
                <div class="row">
                    <?php
                    include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/BigVideoButtons.php';
                    ?>
                </div>
            </div>
        </div>
    <?php
    }
} else if (!empty($_GET['showOnly'])) {
    ?>
    <a href="<?php echo getHomePageURL(); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
    <?php
} else {
    $ads1 = getAdsLeaderBoardTop();
    if (!empty($ads1)) {
    ?>
        <div class="text-center" style="padding: 10px;">
            <?php echo $ads1; ?>
        </div>
<?php
    }
}
?>
