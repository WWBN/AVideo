<?php
$bigVideoAd = getAdsLeaderBoardBigVideo();

$colClass = "col-md-4 col-sm-6";
if (empty($obj->landscapePosters) && !empty($images->posterPortrait)) {
    ?>
    <div class="<?php echo $colClass; ?>  hidden-xs">
        <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="" />
    </div>
    <?php
} else {
    ?>
    <div class="<?php echo $colClass; ?>">
        <a href="<?php echo YouPHPFlix2::getLinkToVideo($video['id']); ?>">
            <div class="thumbsImage hidden-xs">
                <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait thumbsJPG" src="<?php echo $images->poster; ?>" />
                <?php if (!empty($images->thumbsGif)) { ?>
                    <img style="position: absolute; top: 0; display: none;" src="<?php echo $images->thumbsGif; ?>"  alt="<?php echo $video['title']; ?>" id="thumbsGIFBig<?php echo $video['id']; ?>" class="thumbsGIF img-responsive img" />
                <?php } ?>
                <?php if (!empty($obj->BigVideoPlayIcon)) { ?>
                    <i class="far fa-play-circle" style="font-size: 100px; position: absolute; left: 50%; top: 50%; margin-left: -50px; margin-top: -50px;opacity: .6;
                       text-shadow: 0px 0px 30px rgba(0, 0, 0, 0.5);"></i>
                   <?php } ?>
            </div>
        </a>
    </div>
    <?php
}
if (empty($obj->RemoveBigVideoDescription)) {
    ?>
    <div class="infoText col-md-4 col-sm-6 hidden-xs  ">
        <h4 class="mainInfoText" itemprop="description">
            <?php
            echo $video['descriptionHTML'];
            ?>
        </h4>
        <?php
        if (AVideoPlugin::isEnabledByName("VideoTags")) {
            echo VideoTags::getLabels($video['id']);
        }
        ?>
    </div>
    <?php
}
if(!empty($bigVideoAd)) {
    ?>
    <div class="clearfix visible-sm"></div>
    <div class="col-md-4">
        <?php echo $bigVideoAd; ?>
    </div>
    <?php
}
?>