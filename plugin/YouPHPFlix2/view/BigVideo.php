<?php
$uid = uniqid();
$video = Video::getVideo("", "viewableNotUnlisted", true, false, true);
if (empty($video)) {
    $video = Video::getVideo("", "viewableNotUnlisted", true);
}
if ($obj->BigVideo && empty($_GET['showOnly'])) {
    $name = User::getNameIdentificationById($video['users_id']);
    $images = Video::getImageFromFilename($video['filename'], $video['type']);
    $imgGif = $images->thumbsGif;
    $poster = $images->poster;
    //var_dump($video);
    $canWatchPlayButton = "";
    if (User::canWatchVideo($video['id'])) {
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
         position: relative;" >
         <?php
         if (!isMobile() && !empty($video['trailer1'])) {
             ?>
            <div id="bg_container" >
                <iframe src="<?php echo parseVideos($video['trailer1'], 1, 1, 1, 0, 0); ?>" frameborder="0"  allowtransparency="true" allow="autoplay"></iframe>
            </div>
            <div id="bg_container_overlay" ></div>
            <div class="posterDetails " style=" padding: 30px;
             background: -webkit-linear-gradient(left, rgba(<?php echo $obj->backgroundRGB; ?>,0.7) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: -o-linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,0.7) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: linear-gradient(right, rgba(<?php echo $obj->backgroundRGB; ?>,0.7) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);
             background: -moz-linear-gradient(to right, rgba(<?php echo $obj->backgroundRGB; ?>,0.7) 40%, rgba(<?php echo $obj->backgroundRGB; ?>,0) 100%);">
            <?php
        }else{
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
                <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $video['views_count']; ?></span>
                <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $video['likes']; ?></span>
                <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $video['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $video['clean_category']; ?>"><i class="<?php echo $video['iconClass']; ?>"></i> <?php echo $video['category']; ?></a></span>
            </h4>
            <div class="row">                
                <?php
                if (!empty($images->posterPortrait)) {
                    ?>
                    <div class="col-md-2 col-sm-4 col-xs-6">
                        <img alt="<?php echo $video['title']; ?>" class="img img-responsive posterPortrait" src="<?php echo $images->posterPortrait; ?>" />
                    </div>
                    <?php
                }
                ?>
                <div class="infoText col-md-4 col-sm-6 col-xs-6">
                    <h4 class="mainInfoText" itemprop="description">
                        <?php echo nl2br(textToLink($video['description'])); ?>
                    </h4>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-danger playBtn <?php echo $canWatchPlayButton; ?>" href="<?php echo Video::getLinkToVideo($video['id']); ?>"><i class="fa fa-play"></i> <?php echo __("Play"); ?></a>
                    <?php
                    if (!empty($video['trailer1'])) {
                        ?>
                        <a href="#" class="btn btn-warning" onclick="flixFullScreen('<?php echo parseVideos($video['trailer1'], 1, 0, 0, 0, 1); ?>');return false;">
                            <span class="fa fa-film"></span> <?php echo __("Trailer"); ?>
                        </a>
                        <?php
                    }
                    ?>
                    <a href="#" class="btn btn-primary" id="addBtn<?php echo $value['id'] . $uid; ?>" data-placement="right" onclick="loadPlayLists('<?php echo $value['id'] . $uid; ?>', '<?php echo $value['id']; ?>');">
                        <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                    </a>
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
        </div>
    </div>
    <div class="progress" style="height: 3px;">
        <div class="progress-bar progress-bar-danger" role="progressbar" style="width: <?php echo $video['progress']['percent'] ?>%;" aria-valuenow="<?php echo $video['progress']['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
    <?php
} else if (!empty($_GET['showOnly'])) {
    ?>
    <a href="<?php echo $global['webSiteRootURL']; ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?php echo __("Go Back"); ?></a>
    <?php
}
