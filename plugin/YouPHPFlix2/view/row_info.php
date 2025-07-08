<?php
$timeLog6Limit = 0.4;
$timeLog6 = "row_info.php {$value['clean_title']}";
TimeLogStart($timeLog6);
//var_dump(debug_backtrace());
?>
<!-- row_info start -->
<div class="infoDetails">
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
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    foreach ($value['tags'] as $value2) {
        $value2 = (object) $value2;
        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayGroupsTags)) {
            if ($value2->label === __("Group")) {
                ?>
                <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                <?php
            }
        }
        if ($advancedCustom->paidOnlyFreeLabel && !empty($value2->label) && $value2->label === __("Paid Content") && !empty($value2->type) && !empty($value2->text)) {
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
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    ?>
    <?php
    Video::getRratingHTML($value['rrating'] );
    ?>
</div>
<div class="row">
    <?php
    if (!empty($images->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($images->posterPortrait)) {
        ?>
        <!-- row video 1 -->
        <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
            <center>
                <img alt="<?php echo str_replace('"', '', $value['title']); ?>" class="img img-responsive posterPortrait row_info_<?php echo __LINE__; ?>" src="<?php echo $images->posterPortraitThumbs; ?>" style="min-width: 86px;" />
            </center>
        </div>
        <?php
    } else if (!empty($images->poster) && !ImagesPlaceHolders::isDefaultImage($images->poster)) {
        ?>
        <!-- row video 2 -->
        <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
            <center>
                <img alt="<?php echo str_replace('"', '', $value['title']); ?>" class="img img-responsive row_info_<?php echo __LINE__; ?>" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
            </center>
        </div>
        <?php
    } else if (empty($obj->landscapePosters) && !empty($images->posterPortrait)) {
        ?>
        <!-- row video 3 -->
        <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
            <center>
                <img alt="<?php echo str_replace('"', '', $value['title']); ?>" class="img img-responsive posterPortrait row_info_<?php echo __LINE__; ?>" src="<?php echo $images->posterPortraitThumbs; ?>" style="min-width: 86px;" />
            </center>
        </div>
        <?php
    } else {
        ?>
        <!-- row video 4 -->
        <div class="col-md-2 col-sm-3 col-xs-4 hidden-xs">
            <center>
                <img alt="<?php echo str_replace('"', '', $value['title']); ?>" class="img img-responsive row_info_<?php echo __LINE__; ?>" src="<?php echo $images->poster; ?>" style="min-width: 86px;" />
            </center>
        </div>
        <?php
    }
    ?>
    <div class="infoText col-md-4 col-sm-6 col-xs-8">
        <h4 class="mainInfoText" itemprop="description">
            <?php
            if (strip_tags($value['description']) != $value['description']) {
                echo strip_specific_tags($value['description']);
            } else {
                echo nl2br(textToLink(htmlentities($value['description'])));
            }
            ?>
        </h4>
        <?php
        TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
        if (AVideoPlugin::isEnabledByName("VideoTags")) {
            echo VideoTags::getLabels($value['id']);
        }
        TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
        ?>
    </div>
</div>
<div class="footerBtn">
    <?php
    $canWatchPlayButton = "";
    $get = $_GET;
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    if (User::canWatchVideoWithAds($value['id'])) {
        $canWatchPlayButton = "canWatchPlayButton";
    } else if ($obj->hidePlayButtonIfCannotWatch) {
        $canWatchPlayButton = "hidden";
        if (!User::isLogged()) {
            $url = "{$global['webSiteRootURL']}user";
            $url = addQueryStringParameter($url, 'redirectUri', $rowLink);
            ?>
            <a class="btn btn-default"
               href="<?php echo $url; ?>">
                <i class="fas fa-sign-in-alt"></i>
                <span class="hidden-xs"><?php echo __("Login"); ?></span>
            </a>
            <?php
        }
    }
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    $_GET = $get;
    if($rowLinkType === Video::$videoTypePdf){
    ?>
    <button class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>"
       onclick="avideoModalIframe('<?php echo $rowLinkEmbed; ?>');return false;">
        <i class="fas fa-file-pdf"></i>
        <span class="hidden-xs"><?php echo __("Open PDF"); ?></span>
    </button>
    <?php
    }else{
    ?>
    <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>"
       href="<?php echo $rowLink; ?>"
       embed="<?php echo $rowLinkEmbed; ?>">
        <i class="fa fa-play"></i>
        <span class="hidden-xs"><?php echo __("Play"); ?></span>
    </a>
    <?php
    }
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
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    echo AVideoPlugin::getNetflixActionButton($value['id']);
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    getSharePopupButton($value['id']);
    TimeLogEnd($timeLog6, __LINE__, $timeLog6Limit);
    ?>
    <span style="margin-left: 5px;">
    <?php
        echo Video::generatePlaylistButtons($value['id'], 'btn btn-dark', 'background-color: #11111199; ', false);
    ?>
    </span>
</div>
<!-- row_info end -->
