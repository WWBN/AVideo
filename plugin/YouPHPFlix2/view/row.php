<?php
$uid = uniqid();
$landscape = "rowPortrait";
if (!empty($obj->landscapePosters)) {
    $landscape = "landscapeTile";
}
?>
<div class="carousel <?php echo $landscape; ?>" data-flickity='<?php echo json_encode($dataFlickirty) ?>'>
    <?php
    foreach ($videos as $value) {
        $images = Video::getImageFromFilename($value['filename'], $value['type']);
        $imgGif = $images->thumbsGif;
        $img = $images->thumbsJpg;
        $poster = $images->poster;
        $cssClass = "";
        if (!empty($images->posterPortrait)) {
            $imgGif = $images->gifPortrait;
            $img = $images->posterPortrait;
            $cssClass = "posterPortrait";
        }
        ?>
        <div class="carousel-cell  ">
            <div class="tile">
                <div class="slide thumbsImage" crc="<?php echo $value['id'] . $uid; ?>" videos_id="<?php echo $value['id']; ?>" poster="<?php echo $poster; ?>" href="<?php echo Video::getLink($value['id'], $value['clean_title']); ?>"  video="<?php echo $value['clean_title']; ?>" iframe="<?php echo $global['webSiteRootURL']; ?>videoEmbeded/<?php echo $value['clean_title']; ?>">
                    <div class="tile__media ">
                        <img alt="<?php echo $value['title']; ?>" src="<?php echo $global['webSiteRootURL']; ?>view/img/placeholder-image.png" class="tile__img <?php echo $cssClass; ?> thumbsJPG img img-responsive carousel-cell-image" data-flickity-lazyload="<?php echo $img; ?>" />
                        <?php if (!empty($imgGif)) { ?>
                            <img style="position: absolute; top: 0; display: none;" src="<?php echo $global['webSiteRootURL']; ?>view/img/placeholder-image.png"  alt="<?php echo $value['title']; ?>" id="tile__img thumbsGIF<?php echo $value['id']; ?>" class="thumbsGIF img-responsive img carousel-cell-image" data-flickity-lazyload="<?php echo $imgGif; ?>" />
                        <?php } ?>
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $value['progress']['percent'] ?>%;" aria-valuenow="<?php echo $value['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="arrow-down" style="display: none;"></div>
            </div>
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
    if (User::canWatchVideo($value['id'])) {
        $canWatchPlayButton = "canWatchPlayButton";
    }
    ?>
    <div class="poster" id="poster<?php echo $value['id'] . $uid; ?>" style="display: none; background-image: url(<?php echo $poster; ?>);">
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
                if (empty($advancedCustom->doNotDisplayViews)) {
                    ?> 
                <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $value['views_count']; ?></span>
                <?php } ?>
                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $value['likes']; ?></span>
                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $value['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $value['clean_category']; ?>"><i class="fa"></i> <?php echo $value['category']; ?></a></span>

                <?php
                foreach ($value['tags'] as $value2) {
                    if ($value2->label === __("Group")) {
                        ?>
                        <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                        <?php
                    }
                }
                ?>
            </h4>
            <div class="row">
                <?php
                if (!empty($images->posterPortrait)) {
                    ?>
                    <div class="col-md-2 col-sm-3 col-xs-4">
                        <img alt="<?php echo $value['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" />
                    </div>
                    <?php
                }
                ?>
                <div class="infoText col-md-4 col-sm-6 col-xs-8">
                    <h4 class="mainInfoText" itemprop="description">
                        <?php echo nl2br(textToLink($value['description'])); ?>
                    </h4>
                    <?php
if (YouPHPTubePlugin::isEnabledByName("VideoTags")) {
    echo VideoTags::getLabels($value['id']);
}
?>
                </div>
            </div>
            <div class="footerBtn">
                <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" href="<?php echo YouPHPFlix2::getLinkToVideo($value['id']); ?>"><i class="fa fa-play"></i> <?php echo __("Play"); ?></a>
                <?php
                if (!empty($value['trailer1'])) {
                    ?>
                    <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($value['trailer1'], 1, 0, 0, 0, 1); ?>');return false;">
                        <span class="fa fa-film"></span> <?php echo __("Trailer"); ?>
                    </a>
                    <?php
                }
                ?>
                <a href="#" class="btn btn-primary" id="addBtn<?php echo $value['id'] . $uid; ?>" data-placement="right" onclick="loadPlayLists('<?php echo $value['id'] . $uid; ?>', '<?php echo $value['id']; ?>');">
                    <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                </a>
                <?php
                echo YouPHPTubePlugin::getNetflixActionButton($value['id']);
                ?>
            </div>
        </div>
    </div>
    <div id="webui-popover-content<?php echo $value['id'] . $uid; ?>" style="display: none;" >
        <?php if (User::isLogged()) { ?>
            <form role="form">
                <div class="form-group">
                    <input class="form-control" id="searchinput<?php echo $value['id'] . $uid; ?>" type="search" placeholder="<?php echo __("Search"); ?>..." />
                </div>
                <div id="searchlist<?php echo $value['id'] . $uid; ?>" class="list-group">
                </div>
            </form>
            <div>
                <hr>
                <div class="form-group">
                    <input id="playListName<?php echo $value['id'] . $uid; ?>" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                </div>
                <div class="form-group">
                    <?php echo __("Make it public"); ?>
                    <div class="material-switch pull-right">
                        <input id="publicPlayList<?php echo $value['id'] . $uid; ?>" name="publicPlayList" type="checkbox" checked="checked"/>
                        <label for="publicPlayList" class="label-success"></label>
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-success btn-block" id="addPlayList<?php echo $value['id'] . $uid; ?>" ><?php echo __("Create a New Play List"); ?></button>
                </div>
            </div>
        <?php } else { ?>
            <h5><?php echo __("Want to watch this again later?"); ?></h5>
            <?php echo __("Sign in to add this video to a playlist."); ?>
            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                <span class="fas fa-sign-in-alt"></span>
                <?php echo __("Login"); ?>
            </a>
        <?php } ?>
    </div>
    <script>
        $(document).ready(function () {
            loadPlayLists('<?php echo $value['id'] . $uid; ?>', '<?php echo $value['id']; ?>');
            $('#addBtn<?php echo $value['id'] . $uid; ?>').webuiPopover({url: '#webui-popover-content<?php echo $value['id'] . $uid; ?>'});
            $('#addPlayList<?php echo $value['id'] . $uid; ?>').click(function () {
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistAddNew.json.php',
                    method: 'POST',
                    data: {
                        'videos_id': <?php echo $value['id']; ?>,
                        'status': $('#publicPlayList<?php echo $value['id'] . $uid; ?>').is(":checked") ? "public" : "private",
                        'name': $('#playListName<?php echo $value['id'] . $uid; ?>').val()
                    },
                    success: function (response) {
                        if (response.status === "1") {
                            playList = [];
                            console.log(1);
                            reloadPlayLists();
                            loadPlayLists('<?php echo $value['id'] . $uid; ?>', '<?php echo $value['id']; ?>');
                            $('#playListName<?php echo $value['id'] . $uid; ?>').val("");
                            $('#publicPlayList<?php echo $value['id'] . $uid; ?>').prop('checked', true);
                        }
                        modal.hidePleaseWait();
                    }
                });
                return false;
            });
        });
    </script>        
    <?php
}


