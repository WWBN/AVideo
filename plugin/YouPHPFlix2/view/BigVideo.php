<?php
global $advancedCustom;
$uid = uniqid();
$video = Video::getVideo("", "viewableNotUnlisted", true, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotUnlisted", true, true);
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    if (empty($video)) {
        ?>
        <center>
            <img src="<?php echo $global['webSiteRootURL']; ?>view/img/this-video-is-not-available.jpg">
        </center>
        <?php
    } else {
        $name = User::getNameIdentificationById($video['users_id']);
        $images = Video::getImageFromFilename($video['filename'], $video['type']);
        $imgGif = $images->thumbsGif;
        $poster = $images->poster;
        $canWatchPlayButton = "";
        $get = $_GET;
        if (User::canWatchVideoWithAds($video['id'])) {
            $canWatchPlayButton = "canWatchPlayButton";
        }
        $_GET = $get;
        ?>
        <div style="padding-bottom: 40%;"></div>
        <div class="embed-responsive-16by9" id="bigVideo"
             style="background-color: rgb(<?php echo $obj->backgroundRGB; ?>);
             background: url(<?php echo $poster; ?>);
             -webkit-background-size: cover;
             -moz-background-size: cover;
             -o-background-size: cover;
             background-size: cover;
             z-index: 0;
             position: absolute;
             top: 0;
             width: 100%;" >
             <?php
             if (!isMobile() && !empty($video['trailer1'])) {
                 $percent = 2;
                 ?>
                <div id="bg_container" class="" style="height: 100%;" >
                    <iframe src="<?php echo parseVideos($video['trailer1'], 1, 1, 1, 0, 0, 0, 'cover'); ?>"
                            frameborder="0"  allowtransparency="true"
                            allow="autoplay"></iframe>
                </div>
                <div id="bg_container_overlay" ></div>
                <?php
            } else {
                $percent = 40;
            }
            ?>

            <div class="posterDetails" style="
                 padding: 60px 20px 56.25% 20px;
                 background: -webkit-linear-gradient(left, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: -o-linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: -moz-linear-gradient(to right, rgba(<?php echo $obj->backgroundRGB; ?>,1) <?php echo $percent; ?>%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 ">
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
    <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
    <?php
} else {
    $ads1 = getAdsLeaderBoardTop();
    if (!empty($ads1)) {
        ?>
        <div class="row text-center" style="padding: 10px;">
            <?php echo $ads1; ?>
        </div>
        <?php
    }
}
?>