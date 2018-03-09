<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';

if (empty($_GET['user_id'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
}
$user_id = $_GET['user_id'];


$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}

$user = new User($user_id);
$uploadedVideos = Video::getAllVideos("viewable", $user_id);
$publicOnly = true;
if (User::isLogged() && $user_id == User::getId()) {
    $publicOnly = false;
}
$playlists = PlayList::getAllFromUser($user_id, $publicOnly);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Channel"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>        
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
            /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
            $.widget.bridge('uibutton', $.ui.button);
            $.widget.bridge('uitooltip', $.ui.tooltip);
        </script>
        <!-- users_id = <?php echo $user_id; ?> -->
        <link href="<?php echo $global['webSiteRootURL']; ?>/plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>

    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite list-group-item gallery clear clearfix" >
                <div class="row bg-info profileBg" style="background-image: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(); ?>')">
                    <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                </div>
                <div class="col-md-12">
                    <h1 class="pull-left"><?php echo $user->getNameIdentificationBd(); ?></h1>
                    <span class="pull-right">
                        <?php
                        echo Subscribe::getButton($user_id);
                        ?>
                    </span>
                </div>
                <div class="col-md-12">
                    <?php echo nl2br($user->getAbout()); ?>
                </div>
                <div class="col-md-12">
                    <?php
                    foreach ($playlists as $playlist) {
                        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlist['id']);
                        if (empty($videosArrayId)) {
                            continue;
                        }
                        $videos = Video::getAllVideos("viewable", false, false, $videosArrayId);
                        $videos = PlayList::sortVideos($videos, $videosArrayId);
                        ?>

                        <div class="panel panel-default">
                            <div class="panel-heading">

                                <strong style="font-size: 1em;" class="playlistName"><?php echo $playlist['name']; ?> </strong>
                                <a href="<?php echo $global['webSiteRootURL']; ?>playlist/<?php echo $playlist['id']; ?>" class="btn btn-xs btn-default playAll"><span class="fa fa-play"></span> <?php echo __("Play All"); ?></a>
                                <?php
                                if ($isMyChannel) {
                                    ?>     
                                    <script>
                                        $(function () {
                                            $("#sortable<?php echo $playlist['id']; ?>").sortable({
                                                stop: function (event, ui) {
                                                    modal.showPleaseWait();
                                                    var list = $(this).sortable("toArray");
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>sortPlaylist',
                                                        data: {
                                                            "list": list,
                                                            "playlist_id": <?php echo $playlist['id']; ?>
                                                        },
                                                        type: 'post',
                                                        success: function (response) {
                                                            modal.hidePleaseWait();
                                                        }
                                                    });
                                                }
                                            });
                                            $("#sortable<?php echo $playlist['id']; ?>").disableSelection();
                                        });
                                    </script>  
                                    <div class="pull-right btn-group">
                                        <button class="btn btn-xs btn-info" ><i class="fa fa-info-circle"></i> <?php echo __("Drag and drop to sort"); ?></button>
                                        <button class="btn btn-xs btn-danger deletePlaylist" playlist_id="<?php echo $playlist['id']; ?>" ><span class="fa fa-trash-o"></span> <?php echo __("Delete"); ?></button>
                                        <button class="btn btn-xs btn-primary renamePlaylist" playlist_id="<?php echo $playlist['id']; ?>" ><span class="fa fa-pencil"></span> <?php echo __("Rename"); ?></button>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="panel-body">

                                <div id="sortable<?php echo $playlist['id']; ?>" style="list-style: none;">
                                    <?php
                                    foreach ($videos as $value) {
                                        $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                        $name = User::getNameIdentificationById($value['users_id']);

                                        $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                        $imgGif = $images->thumbsGif;
                                        $poster = $images->thumbsJpg;
                                        ?>
                                        <li class="col-lg-2 col-md-3 col-sm-4 col-xs-6 galleryVideo " id="<?php echo $value['id']; ?>">
                                            <a class="aspectRatio16_9" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" style="padding: 0; margin: 0;" >
                                                <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" />
                                                <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                            </a>
                                            <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                                <h2><?php echo $value['title']; ?></h2>
                                            </a>
                                            <?php
                                            if ($isMyChannel) {
                                                ?>
                                                <button class="btn btn-xs btn-default btn-block removeVideo" playlist_id="<?php echo $playlist['id']; ?>" video_id="<?php echo $value['id']; ?>">
                                                    <span class="fa fa-trash-o"></span> <?php echo __("Remove"); ?>
                                                </button>
                                                <?php
                                            }
                                            ?>
                                            <div class="text-muted galeryDetails">
                                                <div>
                                                    <?php
                                                    $value['tags'] = Video::getTags($value['id']);
                                                    foreach ($value['tags'] as $value2) {
                                                        if ($value2->label === __("Group")) {
                                                            ?>
                                                            <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div>
                                                    <i class="fa fa-eye"></i>
                                                    <span itemprop="interactionCount">
                                                        <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php
                                                    echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                                    ?>
                                                </div>
                                                <div class="userName">
                                                    <i class="fa fa-user"></i>
                                                    <?php
                                                    echo $name;
                                                    ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php
                            if ($isMyChannel) {
                                ?>
                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos" class="btn btn-success ">
                                    <span class="glyphicon glyphicon-film"></span>
                                    <span class="glyphicon glyphicon-headphones"></span>
                                    <?php echo __("My videos"); ?>
                                </a>
                                <?php
                            } else {
                                echo __("My videos");
                            }
                            ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            foreach ($uploadedVideos as $value) {
                                $img_portrait = ($value['rotation'] === "90" || $value['rotation'] === "270") ? "img-portrait" : "";
                                $name = User::getNameIdentificationById($value['users_id']);

                                $images = Video::getImageFromFilename($value['filename'], $value['type']);
                                $imgGif = $images->thumbsGif;
                                $poster = $images->thumbsJpg;
                                ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6 galleryVideo ">
                                    <a class="aspectRatio16_9" href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" >
                                        <img src="<?php echo $poster; ?>" alt="<?php echo $value['title']; ?>" class="img img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $value['rotation']; ?>" />
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <h2><?php echo $value['title']; ?></h2>
                                    </a>
                                    <div class="text-muted galeryDetails">
                                        <div>
                                            <?php
                                            $value['tags'] = Video::getTags($value['id']);
                                            foreach ($value['tags'] as $value2) {
                                                if ($value2->label === __("Group")) {
                                                    ?>
                                                    <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div>
                                            <i class="fa fa-eye"></i>
                                            <span itemprop="interactionCount">
                                                <?php echo number_format($value['views_count'], 0); ?> <?php echo __("Views"); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <i class="fa fa-clock-o"></i>
                                            <?php
                                            echo humanTiming(strtotime($value['videoCreation'])), " ", __('ago');
                                            ?>
                                        </div>
                                        <div class="userName">
                                            <i class="fa fa-user"></i>
                                            <?php
                                            echo $name;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>    
                </div>
            </div>
        </div>

        <?php
        include 'include/footer.php';
        ?>
        <script>
            var currentObject;
            $(function () {
                $('.removeVideo').click(function () {
                    currentObject = this;
                    swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                        closeOnConfirm: true
                    },
                            function () {
                                modal.showPleaseWait();
                                var playlist_id = $(currentObject).attr('playlist_id');
                                var video_id = $(currentObject).attr('video_id');
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>removeVideoFromPlaylist',
                                    data: {
                                        "playlist_id": playlist_id,
                                        "video_id": video_id
                                    },
                                    type: 'post',
                                    success: function (response) {
                                        $(currentObject).closest('.galleryVideo').fadeOut();
                                        modal.hidePleaseWait();
                                    }
                                });
                            });
                });

                $('.deletePlaylist').click(function () {
                    currentObject = this;
                    swal({
                        title: "<?php echo __("Are you sure?"); ?>",
                        text: "<?php echo __("You will not be able to recover this action!"); ?>",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                        closeOnConfirm: true
                    },
                            function () {
                                modal.showPleaseWait();
                                var playlist_id = $(currentObject).attr('playlist_id');
                                console.log(playlist_id);
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>removePlaylist',
                                    data: {
                                        "playlist_id": playlist_id
                                    },
                                    type: 'post',
                                    success: function (response) {
                                        $(currentObject).closest('.playList').slideUp();
                                        modal.hidePleaseWait();
                                    }
                                });
                            });

                });

                $('.renamePlaylist').click(function () {
                    currentObject = this;
                    swal({
                        title: "<?php echo __("Change Playlist Name"); ?>!",
                        text: "<?php echo __("What is the new name?"); ?>",
                        type: "input",
                        showCancelButton: true,
                        closeOnConfirm: true,
                        inputPlaceholder: "<?php echo __("Playlist name?"); ?>"
                    },
                            function (inputValue) {
                                if (inputValue === false)
                                    return false;

                                if (inputValue === "") {
                                    swal.showInputError("<?php echo __("You need to tell us the new name?"); ?>");
                                    return false
                                }

                                modal.showPleaseWait();
                                var playlist_id = $(currentObject).attr('playlist_id');
                                console.log(playlist_id);
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>renamePlaylist',
                                    data: {
                                        "playlist_id": playlist_id,
                                        "name": inputValue
                                    },
                                    type: 'post',
                                    success: function (response) {
                                        $(currentObject).closest('.playList').find('.playlistName').text(inputValue);
                                        modal.hidePleaseWait();
                                    }
                                });
                                return false;
                            });

                });
            });
        </script>
    </body>
</html>



