<?php
if (!empty($video['users_id']) && User::hasBlockedUser($video['users_id'])) {
    return false;
}
?>
<div class="col-lg-12 col-sm-12 col-xs-12 text-center">
    <?php echo getAdsSideRectangle(); ?>
</div>
<?php
if (!empty($playlist_id)) {
    include $global['systemRootPath'] . 'view/include/playlist.php';
    ?>
    <script>
        $(document).ready(function () {
            setAutoplay(true);
        });
    </script>
<?php } else if (empty($autoPlayVideo)) {
    ?>
    <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" style="margin: 10px 0;" >
        <strong><?php echo __("Autoplay ended"); ?></strong>
        <span class="pull-right">
            <span><?php echo __("Autoplay"); ?></span>
            <span>
                <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
            </span>
            <div class="material-switch pull-right" style="margin-left: 10px;">
                <input type="checkbox" class="saveCookie" name="autoplay" id="autoplay" <?php echo PlayerSkins::isAutoplayEnabled() ? "checked" : ""; ?>>
                <label for="autoplay" class="label-primary"></label>
            </div>
        </span>
    </div>
<?php } else if (!empty($autoPlayVideo)) { ?>
    <div class="row">
        <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" style="margin: 10px 0;" >
            <strong><?php echo __("Up Next"); ?></strong>
            <span class="pull-right">
                <span><?php echo __("Autoplay"); ?></span>
                <span>
                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                </span>
                <div class="material-switch pull-right" style="margin-left: 10px;">
                    <input type="checkbox" class="saveCookie" name="autoplay" id="autoplay" <?php echo PlayerSkins::isAutoplayEnabled() ? "checked" : ""; ?>>
                    <label for="autoplay" class="label-primary"></label>
                </div>
            </span>
        </div>
    </div>
    <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border autoPlayVideo" id="autoPlayVideoDiv"  style="margin: 10px 0; padding: 15px 5px; <?php echo PlayerSkins::isAutoplayEnabled() ? "" : "display: none;"; ?>" >
        <a href="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], "", $get); ?>" title="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="videoLink h6">
            <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage">
                <?php
                $imgGif = "";
                if (file_exists(Video::getStoragePath()."{$autoPlayVideo['filename']}.gif")) {
                    $imgGif = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.gif";
                }
                if ($autoPlayVideo['type'] === "pdf") {
                    $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.png";
                    $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                } else if (($autoPlayVideo['type'] !== "audio") && ($autoPlayVideo['type'] !== "linkAudio")) {
                    $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
                    $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                } else {
                    $img = "".getCDN()."view/img/audio_wave.jpg";
                    $img_portrait = "";
                }
                ?>
                <img src="<?php echo $img; ?>" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" />
                <?php if (!empty($imgGif)) { ?>
                    <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" id="thumbsGIF<?php echo $autoPlayVideo['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" />
                <?php } ?>
                <time class="duration" datetime="<?php echo Video::getItemPropDuration($autoPlayVideo['duration']); ?>"><?php echo Video::getCleanDuration($autoPlayVideo['duration']); ?></time>
            </div>
            <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                <div class="text-uppercase row"><strong class="title"><?php echo $autoPlayVideo['title']; ?></strong></div>
                <div class="details row text-muted" ">
                    <div>
                        <strong><?php echo __("Category"); ?>: </strong>
                        <span class="<?php echo $autoPlayVideo['iconClass']; ?>"></span>
                        <?php echo $autoPlayVideo['category']; ?>
                    </div>

                    <?php
                    if (empty($advancedCustom->doNotDisplayViews)) {
                        ?> 
                        <div>
                            <strong class=""><?php echo number_format($autoPlayVideo['views_count'], 0); ?></strong>
                            <?php echo __("Views"); ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div><?php echo $autoPlayVideo['creator']; ?></div>
                </div>
                <div class="row">
                    <?php
                    if (!empty($autoPlayVideo['tags'])) {
                        foreach ($autoPlayVideo['tags'] as $autoPlayVideo2) {
                            if (is_array($autoPlayVideo2)) {
                                $autoPlayVideo2 = (object) $autoPlayVideo2;
                            }
                            if ($autoPlayVideo2->label === __("Group")) {
                                ?>
                                <span class="label label-<?php echo $autoPlayVideo2->type; ?>"><?php echo $autoPlayVideo2->text; ?></span>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </a>
    </div>
    <?php
}


$modeYouTubeTimeLog['After autoplay and playlist '] = microtime(true) - $modeYouTubeTime;
$modeYouTubeTime = microtime(true);
?>
<div class="clearfix"></div>
    <div class="extraVideos nopadding"  style="margin: 15px 0;"></div>
<div class="clearfix"></div>
<!-- videos List -->
<!--googleoff: all-->
<div id="videosList">
    <?php
    if (empty($playlist_id)) {
        include $global['systemRootPath'] . 'view/videosList.php';
    }
    ?>
</div>
<!--googleon: all-->
<!-- End of videos List -->

<script>
    var fading = false;
    var autoPlaySources = <?php echo json_encode(@$autoPlaySources); ?>;
    var autoPlayURL = '<?php echo @$autoPlayURL; ?>';
    var autoPlayPoster = '<?php echo @$autoPlayPoster; ?>';
    var autoPlayThumbsSprit = '<?php echo @$autoPlayThumbsSprit; ?>';

    function showAutoPlayVideoDiv() {
        var auto = $("#autoplay").prop('checked');
        if (!auto) {
            $('#autoPlayVideoDiv').slideUp();
        } else {
            $('#autoPlayVideoDiv').slideDown();
        }
    }
    $(document).ready(function () {
        $("input.saveCookie").each(function () {
            var mycookie = Cookies.get($(this).attr('name'));
            if (mycookie && mycookie == "true") {
                $(this).prop('checked', mycookie);
            }
        });
        $("input.saveCookie").change(function () {
            var auto = $(this).prop('checked');
            Cookies.set($(this).attr("name"), auto, {
                path: '/',
                expires: 365
            });
        });

        if (isAutoplayEnabled()) {
            $("#autoplay").prop('checked', true);
        }

        $("#autoplay").change(function () {
            showAutoPlayVideoDiv();
        });
        showAutoPlayVideoDiv();
    });
</script>