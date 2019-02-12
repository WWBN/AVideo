<?php
global $advancedCustom;
$uid = uniqid();
$video = Video::getVideo("", "viewableNotUnlisted", true, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotUnlisted", true, true);
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    $name = User::getNameIdentificationById($video['users_id']);
    $images = Video::getImageFromFilename($video['filename'], $video['type']);
    $imgGif = $images->thumbsGif;
    $poster = $images->poster;
    //var_dump($video);
    $canWatchPlayButton = "";
    if (User::canWatchVideoWithAds($video['id'])) {
        $canWatchPlayButton = "canWatchPlayButton";
    }
    ?>
    <div class="clear clearfix" id="bigVideo" style="background: url(<?php echo $poster; ?>) no-repeat center center fixed; -webkit-background-size: cover;
         -moz-background-size: cover;
         -o-background-size: cover;
         background-size: cover; 
         min-height: 70vh; 
         margin: -20px; 
         margin-bottom: 0; 
         position: relative;
         margin-bottom: -100px;
         z-index: 0;" >
         <?php
         if (!isMobile() && !empty($video['trailer1'])) {
             ?>
            <div id="bg_container" >
                <iframe src="<?php echo parseVideos($video['trailer1'], 1, 1, 1, 0, 0); ?>" frameborder="0"  allowtransparency="true" allow="autoplay"></iframe>
            </div>
            <div id="bg_container_overlay" ></div>
            <div class="posterDetails " style=" padding: 30px;
                 background: -webkit-linear-gradient(bottom, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: -o-linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                 background: -moz-linear-gradient(to top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);">
                 <?php
             } else {
                 ?>
                <div class="posterDetails " style=" padding: 30px; 
                     background: -webkit-linear-gradient(left, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: -o-linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: -moz-linear-gradient(to right, rgba(<?php echo $obj->backgroundRGB; ?>,1) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);">
                 <?php } ?>
                <h2 class="infoTitle" style=""><?php echo $video['title']; ?></h2>
                <h4 class="infoDetails">
                    <?php
                    if (!empty($video['rate'])) {
                        ?>
                        <span class="label label-success"><i class="fab fa-imdb"></i> IMDb <?php echo $video['rate']; ?></span>
                        <?php
                    }
                    ?>

                    <?php
                    if (empty($advancedCustom->doNotDisplayViews)) {
                        ?>
                        <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $video['views_count']; ?></span>
                    <?php } ?>
                    <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $video['likes']; ?></span>
                    <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $video['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $video['clean_category']; ?>"><i class="<?php echo $video['iconClass']; ?>"></i> <?php echo $video['category']; ?></a></span>
                    <?php
                    if (!empty($video['rrating'])) {
                        include $global['systemRootPath'] . 'view/rrating/rating-' . $video['rrating'] . '.php';
                    }else if($advancedCustom->showNotRatedLabel){
                        include $global['systemRootPath'] . 'view/rrating/notRated.php';
                    }
                    ?>
                </h4>
                <div class="row">                
                    <?php
                    if (!empty($images->posterPortrait)) {
                        ?>
                        <div class="col-md-2 col-sm-4 col-xs-6">
                            <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 135px;" />
                        </div>
                        <?php
                    }
                    ?>
                    <div class="infoText col-md-4 col-sm-6 col-xs-6">
                        <h4 class="mainInfoText" itemprop="description">
                            <?php echo nl2br(textToLink($video['description'])); ?>
                        </h4>
                        <?php
                        if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                            echo VideoTags::getLabels($video['id']);
                        }
                        ?>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" href="<?php echo YouPHPFlix2::getLinkToVideo($video['id']); ?>">
                            <i class="fa fa-play"></i> 
                            <span class="hidden-xs"><?php echo __("Play"); ?></span>
                        </a>
                        <?php
                        if (!empty($video['trailer1'])) {
                            ?>
                            <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($video['trailer1'], 1, 0, 0, 0, 1); ?>');return false;">
                                <span class="fa fa-film"></span> 
                                <span class="hidden-xs"><?php echo __("Trailer"); ?></span>
                            </a>
                            <?php
                        }
                        ?>
                        <?php
                        echo YouPHPTubePlugin::getNetflixActionButton($video['id']);
                        ?>
                    </div>
                </div>      
            </div>
        </div>
        <?php
    } else if (!empty($_GET['showOnly'])) {
        ?>
        <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
        <?php
    }
