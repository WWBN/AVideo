<div class="poster rowVideo" id="poster<?php echo $uid; ?>" poster="<?php echo $poster; ?>"
     style="
     display: none;
     background-image: url(<?php echo $global['webSiteRootURL']; ?>plugin/YouPHPFlix2/view/img/loading.gif);
     -webkit-background-size: cover;
     -moz-background-size: cover;
     -o-background-size: cover;
     background-size: cover;
     ">
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
                $value2 = (object) $value2;
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
                <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
                    <center>
                        <img alt="<?php echo $value['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 86px;" />
                    </center>
                </div>
                <?php
            } else if (!empty($images->poster) && basename($images->poster) !== 'notfound.jpg' && basename($images->poster) !== 'pdf.png' && basename($images->poster) !== 'article.png') {
                ?>
                <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
                    <center>
                        <img alt="<?php echo $value['title']; ?>" class="img img-responsive" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
                    </center>
                </div>
                <?php
            } else if (empty($obj->landscapePosters) && !empty($images->posterPortrait)) {
                ?>
                <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
                    <center>
                        <img alt="<?php echo $value['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" style="min-width: 86px;" />
                    </center>
                </div>
                <?php
            } else {
                ?>
                <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
                    <center>
                        <img alt="<?php echo $value['title']; ?>" class="img img-responsive" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
                    </center>
                </div>
                <?php
            }
            ?>
            <div class="infoText col-md-4 col-sm-6 col-xs-8">
                <h4 class="mainInfoText" itemprop="description">
                    <?php
                    if (strip_tags($value['description']) != $value['description']) {
                        echo $value['description'];
                    } else {
                        echo nl2br(textToLink(htmlentities($value['description'])));
                    }
                    ?>
                </h4>
                <?php
                if (AVideoPlugin::isEnabledByName("VideoTags")) {
                    echo VideoTags::getLabels($value['id']);
                }
                ?>
            </div>
        </div>
        <div class="footerBtn">
            <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" 
               href="<?php echo $rowLink; ?>" 
               embed="<?php echo $rowLinkEmbed; ?>">
                <i class="fa fa-play"></i>
                <span class="hidden-xs"><?php echo __("Play"); ?></span>
            </a>
            <?php
            if (!empty($value['trailer1'])) {
                ?>
                <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($value['trailer1'], 1, 0, 0, 0, 1); ?>', '');return false;">
                    <span class="fa fa-film"></span>
                    <span class="hidden-xs"><?php echo __("Trailer"); ?></span>
                </a>
                <?php
            }
            ?>
            <?php
            echo AVideoPlugin::getNetflixActionButton($value['id']);
            getSharePopupButton($value['id']);
            ?>
        </div>
    </div>
</div>