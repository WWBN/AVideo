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
        <div class="flix-hero" id="bigVideo" style="background-image: url('<?php echo $poster; ?>');">
            <?php
            if (!isMobile() && !empty($video['trailer1'])) {
            ?>
                <div id="bg_container">
                    <iframe src="<?php echo addQueryStringParameter(parseVideos($video['trailer1'], 1, 1, 1, 0, 0, 0, 'cover'), 'objectFit', 'cover'); ?>" frameborder="0" allowtransparency="true" allow="autoplay" tabindex="-1" aria-hidden="true"></iframe>
                </div>
                <div id="bg_container_overlay"></div>
            <?php
            }
            ?>

            <div class="posterDetails">
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
