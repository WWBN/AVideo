<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else {
        $_SESSION['type'] = "";
        unset($_SESSION['type']);
    }
}

require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/video_ad.php';
$catLink = "";
if (!empty($_GET['catName'])) {
    $catLink = "cat/{$_GET['catName']}/";
}

$video = Video::getVideo("", "viewableNotAd");
if(empty($_GET['videoName'])){
    $_GET['videoName'] = $video['clean_title'];
}
$obj = new Video("", "", $video['id']);
$resp = $obj->addView();
if (!empty($_GET['playlist_id'])) {
    $playlist_id = $_GET['playlist_id'];
    if (!empty($_GET['playlist_index'])) {
        $playlist_index = $_GET['playlist_index'];
    } else {
        $playlist_index = 0;
    }
    $videosPlayList = Video::getAllVideos("viewableNotAd");
    $video = Video::getVideo($videosPlayList[$playlist_index]['id']);
    if (!empty($videosPlayList[$playlist_index + 1])) {
        $autoPlayVideo = Video::getVideo($videosPlayList[$playlist_index + 1]['id']);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/" . ($playlist_index + 1);
    }
    unset($_GET['playlist_id']);
} else {
    $autoPlayVideo = Video::getRandom($video['id']);
    if (!empty($autoPlayVideo)) {
        $name2 = empty($autoPlayVideo['name']) ? substr($autoPlayVideo['user'], 0, 5) . "..." : $autoPlayVideo['name'];
        $autoPlayVideo['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($autoPlayVideo['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName"><strong>' . $name2 . '</strong> <small>' . humanTiming(strtotime($autoPlayVideo['videoCreation'])) . '</small></div></div>';
        $autoPlayVideo['tags'] = Video::getTags($autoPlayVideo['id']);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . $catLink . "video/" . $autoPlayVideo['clean_title'];
    }
}

if (!empty($video)) {
    $ad = Video_ad::getAdFromCategory($video['categories_id']);
    $name = empty($video['name']) ? substr($video['user'], 0, 5) . "..." : $video['name'];
    $name = "<a href='{$global['webSiteRootURL']}channel/{$video['users_id']}/' class='btn btn-xs btn-default'>{$name}</a>";
    $subscribe = Subscribe::getButton($video['users_id']);

    $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br>' . $subscribe . '<br><small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
    $obj = new Video("", "", $video['id']);
    // dont need because have one embeded video on this page
    //$resp = $obj->addView();
}

if ($video['type'] !== "audio") {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
} else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}

if (!empty($video)) {
    if ($video['type'] !== "audio") {
        $img = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
        $data = @getimagesize("{$global['systemRootPath']}videos/{$video['filename']}.jpg");
        $imgw = $data[0];
        $imgh = $data[1];
    } else {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $video['title']; ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="image_src" href="<?php echo $img; ?>" />
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-rotatezoom/videojs.zoomrotate.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <meta property="fb:app_id"             content="774958212660408" />
        <meta property="og:url"                content="<?php echo $global['webSiteRootURL'], $catLink, "video/", $video['clean_title']; ?>" />
        <meta property="og:type"               content="video.other" />
        <meta property="og:title"              content="<?php echo str_replace('"', '', $video['title']); ?> - <?php echo $config->getWebSiteTitle(); ?>" />
        <meta property="og:description"        content="<?php echo str_replace('"', '', $video['title']); ?>" />
        <meta property="og:image"              content="<?php echo $img; ?>" />
        <meta property="og:image:width"        content="<?php echo $imgw; ?>" />
        <meta property="og:image:height"       content="<?php echo $imgh; ?>" />
        
        <meta property="video:duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
        <meta property="duration" content="<?php echo Video::getItemDurationSeconds($video['duration']); ?>"  />
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="container-fluid principalContainer" itemscope itemtype="http://schema.org/VideoObject">
            <?php
            if (!empty($video)) {
                if (empty($video['type']) || file_exists("{$global['systemRootPath']}videos/{$video['filename']}.mp4")) {
                    $video['type'] = "video";
                }
                require "{$global['systemRootPath']}view/include/{$video['type']}.php";
                $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
                ?>
                <div class="row">
                    <div class="col-sm-1 col-md-1"></div>
                    <div class="col-sm-6 col-md-6">
                        <div class="row bgWhite list-group-item">
                            <div class="row divMainVideo">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <img src="<?php echo $poster; ?>" alt="<?php echo str_replace('"', '', $video['title']); ?>" class="img img-responsive <?php echo $img_portrait; ?> rotate<?php echo $video['rotation']; ?>" height="130" itemprop="thumbnail" />
                                    <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($video['duration']); ?>" ><?php echo Video::getCleanDuration($video['duration']); ?></time>
                                    <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                    <meta itemprop="contentURL" content="<?php echo $global['webSiteRootURL'], $catLink, "video/", $video['clean_title']; ?>" />
                                    <meta itemprop="embedURL" content="<?php echo $global['webSiteRootURL'], "videoEmbeded/", $video['clean_title']; ?>" />
                                    <meta itemprop="uploadDate" content="<?php echo $video['created']; ?>" />
                                    <meta itemprop="description" content="<?php echo str_replace('"', '', $video['title']); ?> - <?php echo $video['description']; ?>" />
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8">
                                    <h1 itemprop="name">
                                        <?php echo $video['title']; ?>
                                        <small>
                                            <?php
                                            if (!empty($video['id'])) {
                                                $video['tags'] = Video::getTags($video['id']);
                                            } else {
                                                $video['tags'] = array();
                                            }
                                            foreach ($video['tags'] as $value) {
                                                if ($value->label === __("Group")) {
                                                    ?>
                                                    <span class="label label-<?php echo $value->type; ?>"><?php echo $value->text; ?></span>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </small>
                                    </h1>
                                    <div class="col-xs-12 col-sm-12 col-md-12"><?php echo $video['creator']; ?></div>
                                    <span class="watch-view-count pull-right text-muted" itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 watch8-action-buttons text-muted">
                                    <button class="btn btn-default no-outline" id="addBtn" data-placement="bottom">
                                        <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                                    </button>
                                    <div class="webui-popover-content">
                                        <?php
                                        if (User::isLogged()) {
                                            ?>
                                            <form role="form">
                                                <div class="form-group">
                                                    <input class="form-control" id="searchinput" type="search" placeholder="Search..." />
                                                </div>
                                                <div id="searchlist" class="list-group">

                                                </div>
                                            </form>
                                            <div >
                                                <hr>
                                                <div class="form-group">
                                                    <input id="playListName" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                                                </div>
                                                <div class="form-group">
                                                    <?php echo __("Make it public"); ?>
                                                    <div class="material-switch pull-right">
                                                        <input id="publicPlayList" name="publicPlayList" type="checkbox" checked="checked"/>
                                                        <label for="publicPlayList" class="label-success"></label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-block" id="addPlayList" ><?php echo __("Create a New Play List"); ?></button>
                                                </div>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <h5>Want to watch this again later?</h5>

                                            Sign in to add this video to a playlist.

                                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                                                <span class="glyphicon glyphicon-log-in"></span>
                                                <?php echo __("Login"); ?>
                                            </a>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <script>
                                        function loadPlayLists() {
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>playLists.json',
                                                success: function (response) {
                                                    $('#searchlist').html('');
                                                    for (var i in response) {
                                                        if (!response[i].id) {
                                                            continue;
                                                        }
                                                        console.log(response[i]);
                                                        var icon = "lock"
                                                        if (response[i].status == "public") {
                                                            icon = "globe"
                                                        }

                                                        var checked = "";
                                                        for (var x in response[i].videos) {
                                                            if (response[i].videos[x].id ==<?php echo $video['id']; ?>) {
                                                                checked = "checked";
                                                            }
                                                        }

                                                        $("#searchlist").append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>' + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault' + response[i].id + '" name="someSwitchOption' + response[i].id + '" class="playListsIds" type="checkbox" value="' + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault' + response[i].id + '" class="label-success"></label></div></a>');
                                                    }
                                                    $('#searchlist').btsListFilter('#searchinput', {itemChild: 'span'});
                                                    $('.playListsIds').change(function () {
                                                        modal.showPleaseWait();
                                                        $.ajax({
                                                            url: '<?php echo $global['webSiteRootURL']; ?>playListAddVideo.json',
                                                            method: 'POST',
                                                            data: {
                                                                'videos_id': <?php echo $video['id']; ?>,
                                                                'add': $(this).is(":checked"),
                                                                'playlists_id': $(this).val()
                                                            },
                                                            success: function (response) {
                                                                console.log(response);
                                                                modal.hidePleaseWait();
                                                            }
                                                        });
                                                        return false;
                                                    });
                                                }
                                            });
                                        }
                                        $(document).ready(function () {
                                            loadPlayLists();
                                            $('#addBtn').webuiPopover();
                                            $('#addPlayList').click(function () {
                                                modal.showPleaseWait();
                                                $.ajax({
                                                    url: '<?php echo $global['webSiteRootURL']; ?>addNewPlayList',
                                                    method: 'POST',
                                                    data: {
                                                        'videos_id': <?php echo $video['id']; ?>,
                                                        'status': $('#publicPlayList').is(":checked") ? "public" : "private",
                                                        'name': $('#playListName').val()
                                                    },
                                                    success: function (response) {
                                                        if (response.status * 1 > 0) {
                                                            // update list
                                                            loadPlayLists();
                                                            $('#searchlist').btsListFilter('#searchinput', {itemChild: 'span'});
                                                            $('#playListName').val("");
                                                            $('#publicPlayList').prop('checked', true);
                                                        }
                                                        modal.hidePleaseWait();
                                                    }
                                                });
                                                return false;
                                            });

                                        });
                                    </script>
                                    <a href="#" class="btn btn-default no-outline" id="shareBtn">
                                        <span class="fa fa-share"></span> <?php echo __("Share"); ?>
                                    </a>
                                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == -1) ? "myVote" : "" ?>" id="dislikeBtn"
                                    <?php
                                    if (!User::isLogged()) {
                                        ?>
                                           data-toggle="tooltip" title="<?php echo __("Don´t like this video? Sign in to make your opinion count."); ?>"
                                       <?php } ?>>
                                        <span class="fa fa-thumbs-down"></span> <small><?php echo $video['dislikes']; ?></small>
                                    </a>			
                                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == 1) ? "myVote" : "" ?>" id="likeBtn"
                                    <?php
                                    if (!User::isLogged()) {
                                        ?>
                                           data-toggle="tooltip" title="<?php echo __("Like this video? Sign in to make your opinion count."); ?>"
                                       <?php } ?>>
                                        <span class="fa fa-thumbs-up"></span> <small><?php echo $video['likes']; ?></small>
                                    </a>
                                    <script>
                                        $(document).ready(function () {

    <?php
    if (User::isLogged()) {
        ?>
                                                $("#dislikeBtn, #likeBtn").click(function () {
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>' + ($(this).attr("id") == "dislikeBtn" ? "dislike" : "like"),
                                                        method: 'POST',
                                                        data: {'videos_id': <?php echo $video['id']; ?>},
                                                        success: function (response) {
                                                            $("#likeBtn, #dislikeBtn").removeClass("myVote");
                                                            if (response.myVote == 1) {
                                                                $("#likeBtn").addClass("myVote");
                                                            } else if (response.myVote == -1) {
                                                                $("#dislikeBtn").addClass("myVote");
                                                            }
                                                            $("#likeBtn small").text(response.likes);
                                                            $("#dislikeBtn small").text(response.dislikes);
                                                        }
                                                    });
                                                    return false;
                                                });
        <?php
    } else {
        ?>
                                                $("#dislikeBtn, #likeBtn").click(function () {

                                                    $(this).tooltip("show");
                                                    return false;
                                                });
        <?php
    }
    ?>

                                        });
                                    </script>
                                </div>
                            </div>
                        </div>

                        <div class="row bgWhite list-group-item" id="shareDiv">
                            <div class="tabbable-panel">
                                <div class="tabbable-line text-muted">
                                    <ul class="nav nav-tabs">
                                        <li class="nav-item">
                                            <a class="nav-link " href="#tabShare" data-toggle="tab">
                                                <span class="fa fa-share"></span>
                                                <?php echo __("Share"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " href="#tabEmbeded" data-toggle="tab">
                                                <span class="fa fa-code"></span>
                                                <?php echo __("Embeded"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tabEmail" data-toggle="tab">
                                                <span class="fa fa-envelope"></span>
                                                <?php echo __("E-mail"); ?>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content clearfix">
                                        <div class="tab-pane active" id="tabShare">
                                            <?php
                                            $url = urlencode($global['webSiteRootURL'] . "{$catLink}video/" . $video['clean_title']);
                                            $title = urlencode($video['title']);
                                            $facebookURL = "https://www.facebook.com/sharer.php?u={$url}&title={$title}";
                                            $twitterURL = "http://twitter.com/home?status={$title}+{$url}";
                                            $googleURL = "https://plus.google.com/share?url={$url}";
                                            ?>
                                            <ul class="social-network social-circle">
                                                <li><a href="<?php echo $facebookURL; ?>" target="_blank" class="icoFacebook" title="Facebook"><i class="fa fa-facebook"></i></a></li>
                                                <li><a href="<?php echo $twitterURL; ?>" target="_blank"  class="icoTwitter" title="Twitter"><i class="fa fa-twitter"></i></a></li>
                                                <li><a href="<?php echo $googleURL; ?>" target="_blank"  class="icoGoogle" title="Google +"><i class="fa fa-google-plus"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="tab-pane" id="tabEmbeded">
                                            <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?>:</h4>
                                            <textarea class="form-control" style="min-width: 100%" rows="5"><?php
                                                if ($video['type'] == 'video' || $video['type'] == 'embed') {
                                                    $code = '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                } else {
                                                    $code = '<iframe width="350" height="40" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                }
                                                echo htmlentities($code);
                                                ?></textarea>
                                        </div>
                                        <div class="tab-pane" id="tabEmail">
                                            <?php
                                            if (!User::isLogged()) {
                                                ?>
                                                <strong>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                                                </strong>
                                                <?php
                                            } else {
                                                ?>
                                                <form class="well form-horizontal" action="<?php echo $global['webSiteRootURL']; ?>sendEmail" method="post"  id="contact_form">
                                                    <fieldset>
                                                        <!-- Text input-->
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                                            <div class="col-md-8 inputGroupContainer">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                                    <input name="email" placeholder="<?php echo __("E-mail Address"); ?>" class="form-control"  type="text">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Text area -->

                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label"><?php echo __("Message"); ?></label>
                                                            <div class="col-md-8 inputGroupContainer">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                                                    <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo _("I would like to share this video with you:"); ?> <?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $video['clean_title']; ?></textarea>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label"><?php echo __("Type the code"); ?></label>
                                                            <div class="col-md-8 inputGroupContainer">
                                                                <div class="input-group">
                                                                    <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha" id="captcha"></span>
                                                                    <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                                                    <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Button -->
                                                        <div class="form-group">
                                                            <label class="col-md-4 control-label"></label>
                                                            <div class="col-md-8">
                                                                <button type="submit" class="btn btn-primary" ><?php echo __("Send"); ?> <span class="glyphicon glyphicon-send"></span></button>
                                                            </div>
                                                        </div>

                                                    </fieldset>
                                                </form>
                                                <script>
                                                    $(document).ready(function () {

                                                        $('#btnReloadCapcha').click(function () {
                                                            $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                                                            $('#captchaText').val('');
                                                        });
                                                        $('#contact_form').submit(function (evt) {
                                                            evt.preventDefault();
                                                            modal.showPleaseWait();
                                                            $.ajax({
                                                                url: '<?php echo $global['webSiteRootURL']; ?>sendEmail',
                                                                data: $('#contact_form').serializeArray(),
                                                                type: 'post',
                                                                success: function (response) {
                                                                    modal.hidePleaseWait();
                                                                    if (!response.error) {
                                                                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");
                                                                    } else {
                                                                        swal("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                                                                    }
                                                                    $('#btnReloadCapcha').trigger('click');
                                                                }
                                                            });
                                                            return false;
                                                        });
                                                    });
                                                </script>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row bgWhite list-group-item">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-lg-12">
                                    <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Category"); ?>:</strong></div>
                                    <div class="col-xs-8 col-sm-10 col-lg-10"><a class="btn btn-xs btn-default"  href="<?php echo $global['webSiteRootURL']; ?>cat/<?php echo $video['clean_category']; ?>"><span class="<?php echo $video['iconClass']; ?>"></span> <?php echo $video['category']; ?></a></div>


                                    <div class="col-xs-4 col-sm-2 col-lg-2 text-right"><strong><?php echo __("Description"); ?>:</strong></div>
                                    <div class="col-xs-8 col-sm-10 col-lg-10" itemprop="description"><?php echo nl2br(textToLink($video['description'])); ?></div>
                                </div>
                            </div>

                        </div>
                        <script>
                            $(document).ready(function () {
                                $("#shareDiv").slideUp();
                                $("#shareBtn").click(function () {
                                    $("#shareDiv").slideToggle();
                                    return false;
                                });
                            });
                        </script>
                        <div class="row bgWhite list-group-item">
                            <div class="input-group">
                                <textarea class="form-control custom-control" rows="3" style="resize:none" id="comment" maxlength="200" <?php
                                if (!User::canComment()) {
                                    echo "disabled";
                                }
                                ?>><?php
                                              if (!User::canComment()) {
                                                  echo __("You cannot comment on videos");
                                              }
                                              ?></textarea>
                                <?php if (User::canComment()) { ?>
                                    <span class="input-group-addon btn btn-success" id="saveCommentBtn" <?php
                                    if (!User::canComment()) {
                                        echo "disabled='disabled'";
                                    }
                                    ?>><span class="glyphicon glyphicon-comment"></span> <?php echo __("Comment"); ?></span>
                                      <?php } else { ?>
                                    <a class="input-group-addon btn btn-success" href="<?php echo $global['webSiteRootURL']; ?>user"><span class="glyphicon glyphicon-log-in"></span> <?php echo __("You must login to be able to comment on videos"); ?></a>
                                <?php } ?>
                            </div>
                            <div class="pull-right" id="count_message"></div>
                            <script>
                                $(document).ready(function () {
                                    var text_max = 200;
                                    $('#count_message').html(text_max + ' <?php echo __("remaining"); ?>');
                                    $('#comment').keyup(function () {
                                        var text_length = $(this).val().length;
                                        var text_remaining = text_max - text_length;
                                        $('#count_message').html(text_remaining + ' <?php echo __("remaining"); ?>');
                                    });
                                });
                            </script>
                            <h4><?php echo __("Comments"); ?>:</h4>
                            <table id="grid" class="table table-condensed table-hover table-striped nowrapCell">
                                <thead>
                                    <tr>
                                        <th data-column-id="comment" ><?php echo __("Comment"); ?></th>
                                    </tr>
                                </thead>
                            </table>

                            <script>
                                $(document).ready(function () {
                                    var grid = $("#grid").bootgrid({
                                        ajax: true,
                                        url: "<?php echo $global['webSiteRootURL'] . "comments.json/" . $video['id']; ?>",
                                        sorting: false,
                                        templates: {
                                            header: ""
                                        }
                                    });
                                    $('#saveCommentBtn').click(function () {
                                        if ($(this).attr('disabled') === 'disabled') {
                                            return false;
                                        }
                                        if ($('#comment').val().length > 5) {
                                            modal.showPleaseWait();
                                            $.ajax({
                                                url: '<?php echo $global['webSiteRootURL']; ?>saveComment',
                                                method: 'POST',
                                                data: {'comment': $('#comment').val(), 'video': "<?php echo $video['id']; ?>"},
                                                success: function (response) {
                                                    if (response.status === "1") {
                                                        swal("<?php echo __("Congratulations"); ?>!", "<?php echo __("Your comment has been saved!"); ?>", "success");
                                                        $('#comment').val('');
                                                        $('#grid').bootgrid('reload');
                                                    } else {
                                                        swal("<?php echo __("Sorry"); ?>!", "<?php echo __("Your comment has NOT been saved!"); ?>", "error");
                                                    }
                                                    modal.hidePleaseWait();
                                                }
                                            });
                                        } else {
                                            swal("<?php echo __("Sorry"); ?>!", "<?php echo __("Your comment must be bigger then 5 characters!"); ?>", "error");
                                        }
                                    });
                                });
                            </script>
                        </div>

                    </div>
                    <div class="col-sm-4 col-md-4 bgWhite list-group-item rightBar">
                        <?php
                        if (!empty($playlist_id)) {
                            include './include/playlist.php';
                            ?>
                            <script>
                                $(document).ready(function () {
                                    Cookies.set('autoplay', true, {
                                        path: '/',
                                        expires: 365
                                    });
                                });
                            </script>

                            <?php
                        } else if (!empty($autoPlayVideo)) {
                            ?>
                            <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" style="display: none;">
                                <strong>
                                    <?php
                                    echo __("Up Next");
                                    ?>
                                </strong>
                                <span class="pull-right">
                                    <span>
                                        <?php
                                        echo __("Autoplay");
                                        ?>
                                    </span>
                                    <span>
                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                                    </span>
                                    <input type="checkbox" data-toggle="toggle" data-size="mini" class="saveCookie" name="autoplay">
                                </span>
                            </div>
                            <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border autoPlayVideo" itemscope itemtype="http://schema.org/VideoObject" style="display: none;" >
                                <a href="<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $autoPlayVideo['clean_title']; ?>" title="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="videoLink">
                                    <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage">
                                        <?php
                                        $imgGif = "";
                                        if (file_exists("{$global['systemRootPath']}videos/{$autoPlayVideo['filename']}.gif")) {
                                            $imgGif = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.gif";
                                        }
                                        if ($autoPlayVideo['type'] !== "audio") {
                                            $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
                                            $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                                        } else {
                                            $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                            $img_portrait = "";
                                        }
                                        ?>
                                        <img src="<?php echo $img; ?>" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" itemprop="thumbnail" />
                                        <?php
                                        if (!empty($imgGif)) {
                                            ?>
                                            <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" id="thumbsGIF<?php echo $autoPlayVideo['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" />
                                        <?php } ?>
                                        <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                        <meta itemprop="contentURL" content="<?php echo $global['webSiteRootURL'], $catLink, "video/", $autoPlayVideo['clean_title']; ?>" />
                                        <meta itemprop="embedURL" content="<?php echo $global['webSiteRootURL'], "videoEmbeded/", $autoPlayVideo['clean_title']; ?>" />
                                        <meta itemprop="uploadDate" content="<?php echo $autoPlayVideo['created']; ?>" />

                                        <time class="duration" itemprop="duration" datetime="<?php echo Video::getItemPropDuration($autoPlayVideo['duration']); ?>"><?php echo Video::getCleanDuration($autoPlayVideo['duration']); ?></time>
                                    </div>
                                    <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                                        <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $autoPlayVideo['title']; ?></strong></div>
                                        <div class="details row text-muted" itemprop="description">
                                            <div>
                                                <strong><?php echo __("Category"); ?>: </strong>
                                                <span class="<?php echo $autoPlayVideo['iconClass']; ?>"></span>
                                                <?php echo $autoPlayVideo['category']; ?>
                                            </div>
                                            <div>
                                                <strong class=""><?php echo number_format($autoPlayVideo['views_count'], 0); ?></strong>
                                                <?php echo __("Views"); ?>
                                            </div>
                                            <div><?php echo $autoPlayVideo['creator']; ?></div>

                                        </div>
                                        <div class="row">
                                            <?php
                                            if (!empty($autoPlayVideo['tags'])) {
                                                foreach ($autoPlayVideo['tags'] as $autoPlayVideo2) {
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
                        ?>
                        <div class="col-lg-12 col-sm-12 col-xs-12">
                            <?php
                            echo $config->getAdsense();
                            ?>
                        </div>
                        <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding">

                        </div>
                        <!-- videos List -->
                        <div id="videosList">
                            <?php include './videosList.php'; ?>
                        </div>
                        <!-- End of videos List -->

                        <script>
                            var fading = false;
                            $(document).ready(function () {

                                $("input.saveCookie").each(function () {
                                    var mycookie = Cookies.get($(this).attr('name'));
                                    console.log($(this).attr('name'));
                                    console.log(mycookie);
                                    if (mycookie && mycookie == "true") {
                                        $(this).prop('checked', mycookie);
                                        $('.autoPlayVideo').slideDown();
                                    }
                                });
                                $("input.saveCookie").change(function () {
                                    console.log($(this).attr('name'));
                                    console.log($(this).prop('checked'));
                                    var auto = $(this).prop('checked');
                                    if (auto) {
                                        $('.autoPlayVideo').slideDown();
                                    } else {
                                        $('.autoPlayVideo').slideUp();
                                    }
                                    Cookies.set($(this).attr("name"), auto, {
                                        path: '/',
                                        expires: 365
                                    });
                                });
                                setTimeout(function () {
                                    $('.autoplay').slideDown();
                                }, 1000);
                                // Total Itens <?php echo $total; ?>

                            });
                        </script>
                    </div>
                    <div class="col-sm-1 col-md-1"></div>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
                </div>
            <?php } ?>

        </div>
        <?php
        include 'include/footer.php';
        ?>

        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-list-filter/bootstrap-list-filter.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    </body>
</html>
