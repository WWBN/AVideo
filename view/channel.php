<?php
global $global, $config, $isChannel;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'plugin/Gallery/functions.php';

if (empty($_GET['channelName'])) {
    if (User::isLogged()) {
        $_GET['user_id'] = User::getId();
    } else {
        return false;
    }
} else {
    $user = User::getChannelOwner($_GET['channelName']);
    if (!empty($user)) {
        $_GET['user_id'] = $user['id'];
    } else {
        $_GET['user_id'] = $_GET['channelName'];
    }
}
$user_id = $_GET['user_id'];

$isMyChannel = false;
if (User::isLogged() && $user_id == User::getId()) {
    $isMyChannel = true;
}

$user = new User($user_id);
$_GET['channelName'] = $user->getChannelName();

$_POST['sort']['created'] = "DESC";

if(empty($_GET['current'])){
    $_POST['current'] = 1;
}else{
    $_POST['current'] = $_GET['current'];
}
$current = $_POST['current'];
$rowCount = 25;
$_POST['rowCount'] = $rowCount;
$uploadedVideos = Video::getAllVideos("a", $user_id);
$uploadedTotalVideos = Video::getTotalVideos("a", $user_id);

$totalPages = ceil($uploadedTotalVideos/$rowCount);

unset($_POST['sort']);
unset($_POST['rowCount']);
unset($_POST['current']);
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
        <style>
            .galleryVideo {
                padding-bottom: 10px;
            }
        </style>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite list-group-item gallery clear clearfix" >
                <div class="row bg-info profileBg" style="background-image: url('<?php echo $global['webSiteRootURL'], $user->getBackgroundURL(); ?>')">
                    <img src="<?php echo User::getPhoto($user_id); ?>" alt="<?php echo $user->_getName(); ?>" class="img img-responsive img-thumbnail" style="max-width: 100px;"/>
                </div>
                <div class="row"><div class="col-6 col-md-12">
                        <h1 class="pull-left">
                            <?php
                            echo $user->getNameIdentificationBd();
                            ?></h1>
                        <span class="pull-right">
                            <?php
                            echo Subscribe::getButton($user_id);
                            ?>
                        </span>
                    </div></div>
                <div class="col-md-12">
                    <?php echo nl2br(htmlentities($user->getAbout())); ?>
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
                            echo YouPHPTubePlugin::getChannelButton();
                            ?>
                        </div>
                        <div class="panel-body">
                            <?php
                            if (!empty($uploadedVideos[0])) {
                                $video = $uploadedVideos[0];
                                $obj = new stdClass();
                                $obj->BigVideo = true;
                                $obj->Description = false;
                                include $global['systemRootPath'] . 'plugin/Gallery/view/BigVideo.php';
                                unset($uploadedVideos[0]);
                            }
                            ?>
                            <div class="row mainArea">
                                <?php
                                createGallerySection($uploadedVideos);
                                ?>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <ul id="channelPagging"></ul>
                            <script>
                                $(document).ready(function () {
                                    $('#channelPagging').bootpag({
                                        total: <?php echo $totalPages; ?>,
                                        page: <?php echo $current; ?>,
                                        maxVisible: 10
                                    }).on('page', function (event, num) {
                                        document.location = ("<?php echo $global['webSiteRootURL']; ?>channel/<?php echo $_GET['channelName']; ?>?current=" + num);
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <script>
                        function refreshPlayLists(container) {
                            var html = '';
                            var isMyChannel = <?php
if (empty($isMyChannel)) {
    echo "false";
} else {
    echo "true";
}
?>;
                            $.ajax({url: "<?php echo $global['webSiteRootURL']; ?>objects/playlists.json.php?isChannel=1", success: function (result) {
                                    jQuery.each(result, function (i, val) {
                                        html += '<div class="panel panel-default">';
                                        html += '<div class="panel-heading">';
                                        html += '<strong style="font-size: 1em;" class="playlistName">' + val.name + '</strong>';
                                        html += '<a href="<?php echo $global['webSiteRootURL']; ?>playlist/' + val.id + '" class="btn-sm btn-light playAll"><span class="fa fa-play"></span> <?php echo __("Play All"); ?></a>';
                                        if (val.pluginBtns != undefined) {
                                            html += val.pluginBtns;
                                        }
                                        if (isMyChannel) {
                                            $(function () {
                                                $("#sortable" + val.id).sortable({
                                                    stop: function (event, ui) {
                                                        modal.showPleaseWait();
                                                        var list = $(this).sortable("toArray");
                                                        $.ajax({
                                                            url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistSort.php',
                                                            data: {
                                                                "list": list,
                                                                "playlist_id": val.id
                                                            },
                                                            type: 'post',
                                                            success: function (response) {
                                                                modal.hidePleaseWait();
                                                            }
                                                        });
                                                    }
                                                });
                                                $("#sortable" + val.id).disableSelection();
                                            });
                                            html += '<div class="pull-right btn-group">';
                                            html += '<button class="btn-sm btn-info" ><i class="fa fa-info-circle"></i> <?php echo __("Drag and drop to sort"); ?></button>';
                                            html += '<button class="btn-sm btn-danger deletePlaylist" playlist_id="' + val.id + '" ><span class="fas fa-trash"></span> <?php echo __("Delete"); ?></button>';
                                            html += '<button class="btn-sm btn-primary renamePlaylist" playlist_id="' + val.id + '" ><span class="fas fa-edit"></span> <?php echo __("Rename"); ?></button>';
                                            html += '</div>';
                                        }
                                        html += '</div><div class="panel-body">';
                                        html += '<div id="sortable' + val.id + '" class="row" style="list-style: none;">';
                                        jQuery.each(val.videos, function (ii, val2) {
                                            html += '<li class="col-lg-2 col-md-4 col-sm-4 col-6 galleryVideo " id="' + val2.videos_id + '">';
                                            html += '<a class="aspectRatio16_9" href="<?php echo $global['webSiteRootURL']; ?>video/' + val2.clean_title + '" title="' + val2.title + '" style="margin: 0;" >';
                                            html += '<img src="<?php echo $global['webSiteRootURL']; ?>videos/' + val2.filename + '_thumbsV2.jpg" alt="' + val2.title + '" class="img img-fluid   rotate' + val2.rotation + '" />';
                                            if (val2.duration == "") {
                                                val2.duration = "00:00:00";
                                            }
                                            html += '<span class="duration">' + val2.duration + '</span></a>';
                                            html += '<a href="<?php echo $global['webSiteRootURL']; ?>video/' + val2.clean_title + '" title="' + val2.title + '">';
                                            html += '<h2>' + val2.title + '</h2></a>';
                                            if (isMyChannel) {
                                                html += '<button class="btn btn-sm btn-warning btn-block removeVideo" playlist_id="' + val.id + '" video_id="' + val2.videos_id + '">';
                                                html += '<span class="fas fa-trash"></span> <?php echo __("Remove"); ?></button>';
                                            }
                                            html += '<div class="text-muted galeryDetails"><div>';
                                            jQuery.each(val2.tags, function (iii, tag) {
                                                if (tag.label == "<?php echo __("Group"); ?>") {
                                                    html += '<span class="badge badge-' + tag.type + '">' + tag.text + '</span>';
                                                }
                                            });
                                            html += '</div><div>';
                                            html += '<i class="fa fa-eye"></i><span itemprop="interactionCount">';
                                            html += val2.views_count + ' <?php echo __("Views"); ?></span></div>';
                                            html += '<div><i class="far fa-clock"></i>' + val2.humancreate + ' ago</div>';
                                            html += '<div><i class="fa fa-user">' + val2.users_id + '</i></div>';
                                            html += '</li>';
                                        });
                                        html += '</div></div></div>';
                                    });
                                    //return html;
                                    $("#" + container).html(html);
                                    initListeners();
                                }});
                        }
                        $(document).ready(function () {
                            refreshPlayLists('playlistContainer');
                        });
                    </script>
                    <div id="playlistContainer">

                    </div>
                </div>
            </div>
        </div>

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            var currentObject;
            function initListeners() {
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
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistRemoveVideo.php',
                                        data: {
                                            "playlist_id": playlist_id,
                                            "video_id": video_id
                                        },
                                        type: 'post',
                                        success: function (response) {
                                            $(".playListsIds" + video_id).prop("checked", false);
                                            $(currentObject).closest('.galleryVideo').fadeOut();
                                            refreshPlayLists('playlistContainer');
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
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistRemove.php',
                                        data: {
                                            "playlist_id": playlist_id
                                        },
                                        type: 'post',
                                        success: function (response) {
                                            $(currentObject).closest('.playList').slideUp();
                                            refreshPlayLists('playlistContainer');
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
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/playlistRename.php',
                                        data: {
                                            "playlist_id": playlist_id,
                                            "name": inputValue
                                        },
                                        type: 'post',
                                        success: function (response) {
                                            $(currentObject).closest('.playList').find('.playlistName').text(inputValue);
                                            refreshPlayLists('playlistContainer');
                                            modal.hidePleaseWait();
                                        }
                                    });
                                    return false;
                                });
                    });
                });
            }
            ;
        </script>
    </body>
</html>
