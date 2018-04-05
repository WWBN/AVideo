<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}
require_once '../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

$img = "{$global['webSiteRootURL']}img/notfound.jpg";
$imgw = 1280;
$imgh = 720;

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    }
    else
        if ($_GET['type'] == 'video') {
            $_SESSION['type'] = 'video';
        }
    else {
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

$video = Video::getVideo("", "viewableNotAd", false, false, true);

if (empty($video)) {
    $video = Video::getVideo("", "viewableNotAd");
}

if (empty($_GET['videoName'])) {
    $_GET['videoName'] = $video['clean_title'];
}

$obj = new Video("", "", $video['id']);

if(empty($_SESSION['type'])){
    $_SESSION['type'] = $video['type'];
}
// $resp = $obj->addView();

if (!empty($_GET['playlist_id'])) {
    $playlist_id = $_GET['playlist_id'];
    if (!empty($_GET['playlist_index'])) {
        $playlist_index = $_GET['playlist_index'];
    }
    else {
        $playlist_index = 0;
    }
    
    $videosArrayId = PlayList::getVideosIdFromPlaylist($_GET['playlist_id']);
    $videosPlayList = Video::getAllVideos("viewableNotAd");
    $videosPlayList = PlayList::sortVideos($videosPlayList, $videosArrayId);
    $video = Video::getVideo($videosPlayList[$playlist_index]['id']);
    if (!empty($videosPlayList[$playlist_index + 1])) {
        $autoPlayVideo = Video::getVideo($videosPlayList[$playlist_index + 1]['id']);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/" . ($playlist_index + 1);
    }
    
    unset($_GET['playlist_id']);
}
else {
    if (!empty($video['next_videos_id'])) {
        $autoPlayVideo = Video::getVideo($video['next_videos_id']);
    }
    else {
        if ($video['category_order'] == 1) {
            unset($_POST['sort']);
            $category = Category::getAllCategories();
            $_POST['sort']['title'] = "ASC";
            
            // maybe there's a more slim method?
            $videos = Video::getAllVideos();
            $videoFound = false;
            $autoPlayVideo;
            foreach($videos as $value) {
                if ($videoFound) {
                    $autoPlayVideo = $value;
                    break;
                }
                
                if ($value['id'] == $video['id']) {       
                    // if the video is found, make another round to have the next video properly.      
                    $videoFound = true;
                }
            }
        }
        else {
            $autoPlayVideo = Video::getRandom($video['id']);
        }
    }
    
    if (!empty($autoPlayVideo)) {
        $name2 = User::getNameIdentificationById($autoPlayVideo['users_id']);
        $autoPlayVideo['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($autoPlayVideo['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName"><strong>' . $name2 . '</strong> <small>' . humanTiming(strtotime($autoPlayVideo['videoCreation'])) . '</small></div></div>';
        $autoPlayVideo['tags'] = Video::getTags($autoPlayVideo['id']);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . $catLink . "video/" . $autoPlayVideo['clean_title'];
    }
}

if (!empty($video)) {
    $ad = Video_ad::getAdFromCategory($video['categories_id']);
    $name = User::getNameIdentificationById($video['users_id']);
    $name = "<a href='{$global['webSiteRootURL']}channel/{$video['users_id']}/' class='btn btn-xs btn-default'>{$name}</a>";
    $subscribe = Subscribe::getButton($video['users_id']);
    $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br />' . $subscribe . '<br /><small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
    $obj = new Video("", "", $video['id']);
    
    // dont need because have one embeded video on this page
    // $resp = $obj->addView();
    
}

if ($video['type'] !== "audio") {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
}
else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}

if (!empty($video)) {
    if ($video['type'] !== "audio") {
        $source = Video::getSourceFile($video['filename']);
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    }
    else {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
}

$advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
?>
<!DOCTYPE html>
<html lang="<?php
echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $video['title']; ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
        <link rel="image_src" href="<?php echo $img; ?>" />
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.css" rel="stylesheet" type="text/css"/>
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
        <?php include 'include/navbar.php'; ?>
        <div class="container-fluid principalContainer" itemscope itemtype="http://schema.org/VideoObject">
            <?php 
            if (!empty($video)) {
                if (empty($video['type'])) {
                    $video['type'] = "video";
                }
            $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
            if (!empty($advancedCustom->showAdsenseBannerOnTop)) {
            ?>
            <style>
                .compress {
                    top: 100px !important;
                }
            </style>
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-xs-12">
                    <center style="margin:5px;">
                    <?php
		                  echo $config->getAdsense();
                        ?>
                    </center>
                </div>
            </div>
            <?php
	           }
            require "{$global['systemRootPath']}view/include/{$video['type']}.php";
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
                                    <meta itemprop="contentURL" content="<?php echo Video::getLink($video['id'], $video['clean_title']); ?>" />
                                    <meta itemprop="embedURL" content="<?php echo Video::getLink($video['id'], $video['clean_title'], true); ?>" />
                                    <meta itemprop="uploadDate" content="<?php echo $video['created']; ?>" />
                                    <meta itemprop="description" content="<?php echo str_replace('"', '', $video['title']); ?> - <?php echo $video['description']; ?>" />
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8">
                                    <h1 itemprop="name">
                                        <?php
	                                       echo $video['title'];
	                                       if (Video::canEdit($video['id'])) {
                                        ?>
                                                <a href="<?php echo $global['webSiteRootURL']; ?>mvideos?video_id=<?php echo $video['id']; ?>" class="btn btn-primary btn-xs" data-toggle="tooltip" title="<?php echo __("Edit Video"); ?>"><i class="fa fa-edit"></i> <?php echo __("Edit Video"); ?></a>
                                            <?php } ?>
                                            <small>
                                            <?php
	                                           if (!empty($video['id'])) {
		                                          $video['tags'] = Video::getTags($video['id']);
	                                           } else {
		                                          $video['tags'] = array();
	                                           }
	                                           foreach($video['tags'] as $value) {
		                                          if ($value->label === __("Group")) { ?>
                                                    <span class="label label-<?php echo $value->type; ?>"><?php echo $value->text; ?></span>
                                                    <?php
		                                          }
	                                           }
                                                ?>
                                            </small>
                                    </h1>
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <?php echo $video['creator']; ?>
                                    </div>
                                    <span class="watch-view-count pull-right text-muted" itemprop="interactionCount"><span class="view-count<?php echo $video['id']; ?>"><?php echo number_format($video['views_count'], 0); ?></span> <?php echo __("Views"); ?></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 watch8-action-buttons text-muted">
                                    <button class="btn btn-default no-outline" id="addBtn" data-placement="bottom">
                                        <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                                    </button>
                                    <div class="webui-popover-content">
                                        <?php if (User::isLogged()) { ?>
                                            <form role="form">
                                                <div class="form-group">
                                                    <input class="form-control" id="searchinput" type="search" placeholder="Search..." />
                                                </div>
                                                <div id="searchlist" class="list-group">
                                                </div>
                                            </form>
                                            <div>
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
                                            <?php } else { ?>
                                            <h5>Want to watch this again later?</h5>

                                            Sign in to add this video to a playlist.

                                            <a href="<?php echo $global['webSiteRootURL']; ?>user" class="btn btn-primary">
                                                <span class="glyphicon glyphicon-log-in"></span>
                                                <?php echo __("Login"); ?>
                                            </a>
                                            <?php } ?>
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
                                                            if (
                                                                    typeof (response[i].videos[x]) === 'object'
                                                                    && response[i].videos[x].videos_id ==<?php echo $video['id']; ?>) {
                                                                checked = "checked";
                                                            }
                                                        }

                                                        $("#searchlist").append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>'
                                                                + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault'
                                                                + response[i].id + '" name="someSwitchOption' + response[i].id + '" class="playListsIds" type="checkbox" value="'
                                                                + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault'
                                                                + response[i].id + '" class="label-success"></label></div></a>');
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
                                    <?php echo YouPHPTubePlugin::getWatchActionButton(); ?>                                    
                                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == - 1) ? "myVote" : "" ?>" id="dislikeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("DonÂ´t like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                                        <span class="fa fa-thumbs-down"></span> <small><?php echo $video['dislikes']; ?></small>
                                    </a>			
                                    <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == 1) ? "myVote" : "" ?>" id="likeBtn" <?php if (!User::isLogged()) { ?> data-toggle="tooltip" title="<?php echo __("Like this video? Sign in to make your opinion count."); ?>" <?php } ?>>
                                        <span class="fa fa-thumbs-up"></span>
                                        <small><?php echo $video['likes']; ?></small>
                                    </a>
                                    <script>
                                        $(document).ready(function () {
                                            <?php if (User::isLogged()) { ?>
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
                                                <?php } else { ?>
                                                $("#dislikeBtn, #likeBtn").click(function () {
                                                    $(this).tooltip("show");
                                                    return false;
                                                });
                                                <?php } ?>
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
                                            <a class="nav-link " href="#tabEmbed" data-toggle="tab">
                                                <span class="fa fa-code"></span>
                                                <?php echo __("Embed"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tabEmail" data-toggle="tab">
                                                <span class="fa fa-envelope"></span>
                                                <?php echo __("E-mail"); ?>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#tabPermaLink" data-toggle="tab">
                                                <span class="fa fa-link"></span>
                                                <?php echo __("Permanent Link"); ?>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content clearfix">
                                        <div class="tab-pane active" id="tabShare">
                                            <?php 
                                                    $url = urlencode($global['webSiteRootURL'] . "{$catLink}video/" . $video['clean_title']);
                                                    $title = urlencode($video['title']);
	                                                include './include/social.php';

                                            ?>
                                        </div>
                                        <div class="tab-pane" id="tabEmbed">
                                            <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?>:</h4>
                                            <textarea class="form-control" style="min-width: 100%" rows="5">
                                                <?php
                                                if ($video['type'] == 'video' || $video['type'] == 'embed') {
                                                    $code = '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . Video::getLink($video['id'], $video['clean_title'], true) . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                }
                                                else {
                                                    $code = '<iframe width="350" height="40" style="max-width: 100%;max-height: 100%;" src="' . Video::getLink($video['id'], $video['clean_title'], true) . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                }
	                                            echo htmlentities($code); ?>
                                            </textarea>
                                        </div>
                                        <div class="tab-pane" id="tabEmail">
                                            <?php if (!User::isLogged()) { ?>
                                                <strong>
                                                    <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                                                </strong>
                                                <?php } else { ?>
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
                                                                    <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo __("I would like to share this video with you:"); ?> <?php echo Video::getLink($video['id'], $video['clean_title']); ?></textarea>
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
                                                <?php } ?>
                                        </div>

                                        <div class="tab-pane" id="tabPermaLink">
                                            <input value="<?php echo Video::getPermaLink($video['id']); ?>" class="form-control" readonly="readonly"/>
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
                            <?php include './videoComments.php'; ?>
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
                           } else if (empty($autoPlayVideo)) { ?>
                                <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" >
                                    <strong><?php echo __("Autoplay ended"); ?></strong>
                                <span class="pull-right">
                                    <span><?php echo __("Autoplay"); ?></span>
                                <span>
                                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                                </span>
                                <input type="checkbox" data-toggle="toggle" data-size="mini" class="saveCookie" name="autoplay">
                                </span>
                            </div>
                            <?php } else if (!empty($autoPlayVideo)) { ?>
                            <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" style="display: none;">
                                <strong><?php echo __("Up Next"); ?></strong>
                                <span class="pull-right">
                                    <span><?php echo __("Autoplay"); ?></span>
                                    <span>
                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                                    </span>
                                    <input type="checkbox" data-toggle="toggle" data-size="mini" class="saveCookie" name="autoplay">
                                </span>
                            </div>
                            <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border autoPlayVideo" itemscope itemtype="http://schema.org/VideoObject" style="display: none;" >
                                <a href="<?php echo $global['webSiteRootURL'], $catLink; ?>video/<?php echo $autoPlayVideo['clean_title']; ?>" title="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="videoLink h6">
                                    <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage">
                                        <?php 
                                            $imgGif = "";
		                                    if (file_exists("{$global['systemRootPath']}videos/{$autoPlayVideo['filename']}.gif")) {
			                                     $imgGif = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.gif";
		                                    }
                                            if ($autoPlayVideo['type'] !== "audio") {
                                                $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
                                                $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                                            }
                                            else {
                                                $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                                $img_portrait = "";
                                            }
                                        ?>
                                        <img src="<?php echo $img; ?>" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" itemprop="thumbnail" />
                                        <?php if (!empty($imgGif)) { ?>
                                        <img src="<?php echo $imgGif; ?>" style="position: absolute; top: 0; display: none;" alt="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" id="thumbsGIF<?php echo $autoPlayVideo['id']; ?>" class="thumbsGIF img-responsive <?php echo $img_portrait; ?>  rotate<?php echo $autoPlayVideo['rotation']; ?>" height="130" />
                                        <?php } ?>
                                        <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                        <meta itemprop="contentURL" content="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title']); ?>" />
                                        <meta itemprop="embedURL" content="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], true); ?>" />
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
			                                     foreach($autoPlayVideo['tags'] as $autoPlayVideo2) {
				                                    if ($autoPlayVideo2->label === __("Group")) {
                                            ?>
                                                        <span class="label label-<?php echo $autoPlayVideo2->type; ?>"><?php echo $autoPlayVideo2->text; ?></span>
                                            <?php }
                                                 }
                                            } ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php
                           } if (!empty($advancedCustom->showAdsenseBannerOnLeft)) { ?>
                            <div class="col-lg-12 col-sm-12 col-xs-12">
                                <?php echo $config->getAdsense(); ?>
                            </div>
                            <?php } ?>
                        <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding"></div>
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
                            });
                        </script>
                    </div>
                    <div class="col-sm-1 col-md-1"></div>
                </div>
                <?php } else { ?>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
                </div>
            <?php } ?>
        </div>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
                        /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
                        $.widget.bridge('uibutton', $.ui.button);
                        $.widget.bridge('uitooltip', $.ui.tooltip);
        </script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-contrib-ads/videojs.ads.min.js" type="text/javascript"></script>
        <?php include 'include/footer.php'; ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-rotatezoom/videojs.zoomrotate.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/webui-popover/jquery.webui-popover.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-list-filter/bootstrap-list-filter.min.js" type="text/javascript"></script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>