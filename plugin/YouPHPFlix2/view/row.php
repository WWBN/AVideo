<?php
global $advancedCustom;
$uid = uniqid();
$landscape = "rowPortrait";
$css = "";
if (!empty($obj->landscapePosters)) {
    $landscape = "landscapeTile";
    if (!empty($obj->titleLabel)) { $css = "height: 185px;"; }
}
$get = $_GET;
$post = $_POST;
?>
<div class="carousel <?php echo $landscape; ?>" data-flickity='<?php echo json_encode($dataFlickirty) ?>' style="<?php echo $css; ?>">
    <?php
    foreach ($videos as $value) {
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $imgGif = $images->thumbsGif;
        $img = $images->thumbsJpg;
        $poster = $images->poster;
        $cssClass = "";
        if (empty($obj->landscapePosters) && !empty($images->posterPortraitThumbs)) {
            $imgGif = $images->gifPortrait;
            $img = $images->posterPortraitThumbs;
            $cssClass = "posterPortrait";
        }
        ?>
        <div class="carousel-cell  "  itemscope itemtype="http://schema.org/VideoObject">
            <div class="tile">
                <div class="slide thumbsImage" crc="<?php echo $value['id'] . $uid; ?>" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title']); ?>"  video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
                    <div class="tile__media ">
                        <img alt="<?php echo $value['title']; ?>" src="<?php echo $global['webSiteRootURL']; ?>view/img/placeholder-image.png" class="tile__img <?php echo $cssClass; ?> thumbsJPG img img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                        <?php if (!empty($imgGif)) { ?>
                            <img style="position: absolute; top: 0; display: none;" src="<?php echo $global['webSiteRootURL']; ?>view/img/placeholder-image.png"  alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                        <?php } ?>
                        <?php
                        if ($advancedCustom->paidOnlyFreeLabel && $obj->paidOnlyLabelOverPoster) {
                            foreach ($value['tags'] as $value2) {
                                if (!empty($value2->label) && $value2->label === __("Paid Content")) {
                                    ?><span class="paidOnlyLabel label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
                                }
                            }
                        }
                        if (!empty($obj->titleLabel)) {
                        ?>  
                        <h4 style="<?php if (!empty($obj->titleLabelOverPoster)) { ?>margin-top: -27px;<?php } echo $obj->titleLabelCSS; ?> "><?php echo $value['title']; ?></h4>
                        <?php
                        }
                        ?>
                        <div class="progress" style="height: 3px; margin-bottom: 2px;">
                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <?php
                        if ($advancedCustom->paidOnlyFreeLabel && !$obj->paidOnlyLabelOverPoster) {
                            foreach ($value['tags'] as $value2) {
                                if (!empty($value2->label) && $value2->label === __("Paid Content")) {
                                    ?><div class="label label-<?php echo $value2->type; ?>" style="margin: 0; margin-top: -2px;  width: 100%; display: block; border-top-left-radius: 0; border-top-right-radius: 0; "><?php echo $value2->text; ?></div><?php
                                }
                            }
                        }
                        ?>  
                    </div>
                </div>
                <div class="arrow-down" style="display: none;"></div>
            </div>
            <?php
            getLdJson($value['id']);
            getItemprop($value['id']);
            ?>
        </div>        
        <?php
    }
    ?>
</div>

<?php
foreach ($videos as $value) {
    $images = Video::getImageFromFilename($value['filename'], $value['type']);
    $imgGif = $images->thumbsGif;
    $img = $images->thumbsJpg;
    $poster = $images->poster;
    $canWatchPlayButton = "";
    if (User::canWatchVideoWithAds($value['id'])) {
        $canWatchPlayButton = "canWatchPlayButton";
    }
    ?>
    <div class="poster" id="poster<?php echo $value['id'] . $uid; ?>" poster="<?php echo $poster; ?>" style="display: none; background-image: url(<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/img/loading.gif);">
        <div class="posterDetails " style="
             background: -webkit-linear-gradient(left, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: -o-linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: -moz-linear-gradient(to right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);">
            <h2 class="infoTitle"><?php echo $value['title']; ?></h2>
            <h4 class="infoDetails">
                <?php
                if (!empty($value['rate'])) {
                    ?>
                    <span class="label label-success"><i class="fab fa-imdb"></i> IMDb <?php echo $value['rate']; ?></span>
                    <?php
                }
                ?>

                <?php
                if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayViews)) {
                    ?> 
                    <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                <?php } ?>
                <?php
                if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayLikes)) {
                    ?>
                    <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                <?php } ?>
                <?php
                if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayCategory)) {
                    ?>
                    <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category']; ?>"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['category']; ?></a></span>                       
                <?php } ?>
                <?php
                foreach ($value['tags'] as $value2) {
                    if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayGroupsTags)) {
                        if ($value2->label === __("Group")) {
                            ?>
                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                            <?php
                        }
                    }
                    if ($advancedCustom->paidOnlyFreeLabel && !empty($value2->label) && $value2->label === __("Paid Content")) {
                        ?><span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
                    }
                    if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayPluginsTags)) {

                        if ($value2->label === "Plugin") {
                            ?>
                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                            <?php
                        }
                    }
                }
                ?>   
                <?php
                if (!empty($value['rrating'])) {
                    include $global['systemRootPath'] . 'view/rrating/rating-' . $value['rrating'] . '.php';
                } else if (!empty($advancedCustom) && $advancedCustom->showNotRatedLabel) {
                    include $global['systemRootPath'] . 'view/rrating/notRated.php';
                }
                ?>
            </h4>
            <div class="row">
                <?php
                if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4">
                        <center>
                            <img alt="<?php echo $value['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 86px;" />
                        </center>
                    </div>
                    <?php
                } else if (!empty($images->poster) && basename($images->poster) !== 'notfound.jpg' && basename($images->poster) !== 'pdf.png' && basename($images->poster) !== 'article.png') {
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4">
                        <center>
                            <img alt="<?php echo $value['title']; ?>" class="img img-responsive" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
                        </center>
                    </div>
                    <?php
                } else if (empty($obj->landscapePosters) && !empty($images->posterPortrait)) {
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4">
                        <center>
                            <img alt="<?php echo $value['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 86px;" />
                        </center>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4">
                        <center>
                            <img alt="<?php echo $value['title']; ?>" class="img img-responsive" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
                        </center>
                    </div>
                    <?php
                }
                ?>
                <div class="infoText col-md-4 col-sm-6 col-xs-8">
                    <h4 class="mainInfoText" itemprop="description">
                        <?php echo $value['description']; ?>
                    </h4>
                    <?php
                    if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                        echo VideoTags::getLabels($value['id']);
                    }
                    ?>
                </div>
            </div>
            <div class="footerBtn">
                <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" href="<?php echo YouPHPFlix2::getLinkToVideo($value['id']); ?>">
                    <i class="fa fa-play"></i> 
                    <span class="hidden-xs"><?php echo __("Play"); ?></span>
                </a>
                <?php
                if (!empty($value['trailer1'])) {
                    ?>
                    <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($value['trailer1'], 1, 0, 0, 0, 1); ?>');return false;">
                        <span class="fa fa-film"></span> 
                        <span class="hidden-xs"><?php echo __("Trailer"); ?></span>
                    </a>
                    <?php
                }
                ?>
                <?php
                echo YouPHPTubePlugin::getNetflixActionButton($value['id']);
                ?>
            </div>
        </div>
    </div>     
    <?php
}

$_GET = $get;
$_POST = $post;
