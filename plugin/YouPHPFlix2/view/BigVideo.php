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
        <div class="clear clearfix" id="bigVideo" style="background: url(<?php echo $poster; ?>) no-repeat center center fixed; -webkit-background-size: cover;
             -moz-background-size: cover;
             -o-background-size: cover;
             background-size: cover; 
             height: 0;
             padding-bottom: 56.25%;/* Aspect ratio */
             margin: -120px -20px; 
             margin-bottom: 0; 
             position: relative;
             margin-bottom: -200px;
             z-index: 0;" >
             <?php
             if (!isMobile() && !empty($video['trailer1'])) {
                 ?>
                <div id="bg_container" >
                    <iframe src="<?php echo parseVideos($video['trailer1'], 1, 1, 1, 0, 0, 'fill'); ?>" frameborder="0"  allowtransparency="true" allow="autoplay"></iframe>
                </div>
                <div id="bg_container_overlay" ></div>
                <div class="posterDetails " style=" padding: 30px; padding-top: 120px;
                     background: -webkit-linear-gradient(bottom, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: -o-linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: linear-gradient(top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
                     background: -moz-linear-gradient(to top, rgba(<?php echo $obj->backgroundRGB; ?>,1) 2%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);">
                     <?php
                 } else {
                     ?>
                    <div class="posterDetails " style=" padding: 30px;  padding-top: 120px;
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
                        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayViews)) {
                            ?>
                            <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $video['views_count']; ?></span>
                        <?php } ?>
                        <?php
                        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayLikes)) {
                            ?>
                            <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $video['likes']; ?></span>                        
                        <?php } ?>
                        <?php
                        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayCategory)) {
                            ?>
                            <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $video['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $video['clean_category']; ?>"><i class="<?php echo $video['iconClass']; ?>"></i> <?php echo $video['category']; ?></a></span>                       
                        <?php } ?>
                        <?php
                        foreach ($video['tags'] as $value2) {
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
                        if (!empty($video['rrating'])) {
                            include $global['systemRootPath'] . 'view/rrating/rating-' . $video['rrating'] . '.php';
                        } else if (!empty($advancedCustom) && $advancedCustom->showNotRatedLabel) {
                            include $global['systemRootPath'] . 'view/rrating/notRated.php';
                        }
                        ?>
                    </h4>
                    <div class="row">                
                        <?php
                        $colClass = "col-md-2 col-sm-4 col-xs-6";
                        if (!empty($obj->RemoveBigVideoDescription)) {
                            $colClass = "col-md-4 col-sm-4 col-xs-6";
                        }
                        if (empty($obj->landscapePosters) && !empty($images->posterPortrait)) {
                            ?>
                            <div class="<?php echo $colClass; ?>">
                                <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 135px;" />
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="<?php echo $colClass; ?>">
                                <a href="<?php echo YouPHPFlix2::getLinkToVideo($video['id']); ?>">
                                    <div class="thumbsImage">
                                        <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait thumbsJPG" src="<?php echo $images->poster; ?>" style="min-width: 135px; height: auto;" />
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
                            <div class="infoText col-md-4 col-sm-6 col-xs-6">
                                <h4 class="mainInfoText" itemprop="description">
                                    <?php echo $video['description']; ?>
                                </h4>
                                <?php
                                if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
                                    echo VideoTags::getLabels($video['id']);
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
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
        }
    } else if (!empty($_GET['showOnly'])) {
        ?>
        <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
        <?php
    }
