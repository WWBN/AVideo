<?php
require_once '../../../videos/configuration.php';

require_once $global['systemRootPath'] . 'objects/user.php';
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
require_once $global['systemRootPath'] . 'objects/subscribe.php';

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 4;
$half = floor($_POST['rowCount'] / 2);
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';
$videos = Video::getAllVideos("viewableNotAd");
foreach ($videos as $key => $value) {
    $videos[$key] = Video::getVideo($value['id']);
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 50px;"/></div><div class="commentDetails" style="margin-left:60px;"><div class="commenterName"><strong><a href="' . User::getChannelLink($value['users_id']) . '/">' . $name . '</a></strong><br><span class="text-muted">' . humanTiming(strtotime($value['videoCreation'])) . '</span></div></div>';
}
$count = 0;
if (!empty($videos)) {
    foreach ($videos as $video) {
        $count++;
        $ad = Video_ad::getAdFromCategory($video['categories_id']);
        $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
        $playNowVideo = $video;
        $transformation = "{rotate:" . $video['rotation'] . ", zoom: " . $video['zoom'] . "}";
        if ($video['rotation'] === "90" || $video['rotation'] === "270") {
            $aspectRatio = "9:16";
            $vjsClass = "vjs-9-16";
            $embedResponsiveClass = "embed-responsive-9by16";
        } else {
            $aspectRatio = "16:9";
            $vjsClass = "vjs-16-9";
            $embedResponsiveClass = "embed-responsive-16by9";
        }

        if (!empty($ad)) {
            $playNowVideo = $ad;
            $logId = Video_ad::log($ad['id']);
        }
        if (($video['type'] !== "audio")&&($video['type'] !== "linkAudio")) {
            $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
        } else {
            $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
        }
        ?>
        <div class="row fbRow">
            <div class="col-md-10 col-md-offset-1 list-group-item">
                <div class="clear clearfix"><?php echo $video['creator']; ?> </div>
                <h2><?php echo $video['title']; ?></h2>
                <div><?php echo nl2br(textToLink($video['description'])); ?></div>
                <div class="main-video embed-responsive <?php
                echo $embedResponsiveClass;
                if (!empty($logId)) {
                    echo " ad";
                }
                ?>">
                         <?php
                         if ($video['type'] === "embed") {
                             ?>
                        <div class="embed-responsive embed-responsive-16by9" id="mainVideo<?php echo $video['id']; ?>" >
                            <iframe id="iframe<?php echo $video['id']; ?>" class="embed-responsive-item" src="<?php echo parseVideos($video['videoLink']) ?>?enablejsapi=1"></iframe>
                        </div>
                        <?php
                    } else {
                        ?>
                        <video poster="<?php echo $poster; ?>" controls 
                               class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                               id="mainVideo<?php echo $video['id']; ?>"  data-setup='{ "aspectRatio": "<?php echo $aspectRatio; ?>" }'>
                                   <?php
                                   echo getSources($playNowVideo['filename']);
                                   ?>
                            <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                            <p class="vjs-no-js">
                                <?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                                <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                            </p>
                        </video>
                        <?php if (!empty($logId)) { ?>
                            <div id="adUrl<?php echo $video['id']; ?>" class="adControl" ><?php echo __("Ad"); ?> <span class="time">0:00</span> <i class="fa fa-info-circle"></i>
                                <a href="<?php echo $global['webSiteRootURL']; ?>objects/video_adClickLog.php?video_ads_logs_id=<?php echo $logId; ?>&adId=<?php echo $ad['id']; ?>" target="_blank" ><?php
                                    $url = parse_url($ad['redirect']);
                                    echo $url['host'];
                                    ?> <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            <a id="adButton<?php echo $video['id']; ?>" href="#" class="adControl" <?php if (!empty($ad['skip_after_seconds'])) { ?> style="display: none;" <?php } ?>><?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
                            <?php
                        }
                    }
                    ?>

                </div>
                <!-- all the stuffs here -->

                <?php echo Subscribe::getButton($video['users_id']); ?>
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

                <span class="watch-view-count pull-right text-muted" itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>

                <div class="row">
                    <div class="col-md-12 col-lg-12 watch8-action-buttons text-muted">
                        <button class="btn btn-default no-outline" id="addBtn<?php echo $video['id']; ?>" data-placement="bottom">
                            <span class="fa fa-plus"></span> <?php echo __("Add to"); ?>
                        </button>
                        <div class="webui-popover-content">
                            <?php
                            if (User::isLogged()) {
                                ?>
                                <form role="form">
                                    <div class="form-group">
                                        <input class="form-control" id="searchinput<?php echo $video['id']; ?>" type="search" placeholder="Search..." />
                                    </div>
                                    <div id="searchlist<?php echo $video['id']; ?>" class="list-group">

                                    </div>
                                </form>
                                <div >
                                    <hr>
                                    <div class="form-group">
                                        <input id="playListName<?php echo $video['id']; ?>" class="form-control" placeholder="<?php echo __("Create a New Play List"); ?>"  >
                                    </div>
                                    <div class="form-group">
                                        <?php echo __("Make it public"); ?>
                                        <div class="material-switch pull-right">
                                            <input id="publicPlayList<?php echo $video['id']; ?>" name="publicPlayList" type="checkbox" checked="checked"/>
                                            <label for="publicPlayList" class="label-success"></label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button class="btn btn-success btn-block" id="addPlayList<?php echo $video['id']; ?>" ><?php echo __("Create a New Play List"); ?></button>
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
                            function loadPlayLists<?php echo $video['id']; ?>() {
                                $.ajax({
                                    url: '<?php echo $global['webSiteRootURL']; ?>playLists.json',
                                    success: function (response) {
                                        $('#searchlist<?php echo $video['id']; ?>').html('');
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

                                            $("#searchlist<?php echo $video['id']; ?>").append('<a class="list-group-item"><i class="fa fa-' + icon + '"></i> <span>' + response[i].name + '</span><div class="material-switch pull-right"><input id="someSwitchOptionDefault<?php echo $video['id']; ?>' + response[i].id + '" name="someSwitchOption' + response[i].id + '" class="playListsIds" type="checkbox" value="' + response[i].id + '" ' + checked + '/><label for="someSwitchOptionDefault<?php echo $video['id']; ?>' + response[i].id + '" class="label-success"></label></div></a>');
                                        }
                                        $('#searchlist<?php echo $video['id']; ?>').btsListFilter('#searchinput<?php echo $video['id']; ?>', {itemChild: 'span'});
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
                                loadPlayLists<?php echo $video['id']; ?>();
                                $('#addBtn<?php echo $video['id']; ?>').webuiPopover();
                                $('#addPlayList<?php echo $video['id']; ?>').click(function () {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>addNewPlayList',
                                        method: 'POST',
                                        data: {
                                            'videos_id': <?php echo $video['id']; ?>,
                                            'status': $('#publicPlayList<?php echo $video['id']; ?>').is(":checked") ? "public" : "private",
                                            'name': $('#playListName<?php echo $video['id']; ?>').val()
                                        },
                                        success: function (response) {
                                            if (response.status * 1 > 0) {
                                                // update list
                                                loadPlayLists<?php echo $video['id']; ?>();
                                                $('#searchlist<?php echo $video['id']; ?>').btsListFilter('#searchinput', {itemChild: 'span'});
                                                $('#playListName<?php echo $video['id']; ?>').val("");
                                                $('#publicPlayList<?php echo $video['id']; ?>').prop('checked', true);
                                            }
                                            modal.hidePleaseWait();
                                        }
                                    });
                                    return false;
                                });

                            });
                        </script>
                        <a href="#" class="btn btn-default no-outline" id="shareBtn<?php echo $video['id']; ?>">
                            <span class="fa fa-share"></span> <?php echo __("Share"); ?>
                        </a>
                        <a href="#" class="btn btn-default no-outline" id="commentBtn<?php echo $video['id']; ?>">
                            <span class="fa fa-comment"></span> <?php echo __("Comment"); ?>
                        </a>
                        <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == -1) ? "myVote" : "" ?>" id="dislikeBtn<?php echo $video['id']; ?>"
                        <?php
                        if (!User::isLogged()) {
                            ?>
                               data-toggle="tooltip" title="<?php echo __("Don´t like this video? Sign in to make your opinion count."); ?>"
                           <?php } ?>>
                            <span class="fa fa-thumbs-down"></span> <small><?php echo $video['dislikes']; ?></small>
                        </a>			
                        <a href="#" class="btn btn-default no-outline pull-right <?php echo ($video['myVote'] == 1) ? "myVote" : "" ?>" id="likeBtn<?php echo $video['id']; ?>"
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
                                    $("#dislikeBtn<?php echo $video['id']; ?>, #likeBtn<?php echo $video['id']; ?>").click(function () {
                                        $.ajax({
                                            url: '<?php echo $global['webSiteRootURL']; ?>' + ($(this).attr("id") == "dislikeBtn<?php echo $video['id']; ?>" ? "dislike" : "like"),
                                            method: 'POST',
                                            data: {'videos_id': <?php echo $video['id']; ?>},
                                            success: function (response) {
                                                $("#likeBtn<?php echo $video['id']; ?>, #dislikeBtn<?php echo $video['id']; ?>").removeClass("myVote");
                                                if (response.myVote == 1) {
                                                    $("#likeBtn<?php echo $video['id']; ?>").addClass("myVote");
                                                } else if (response.myVote == -1) {
                                                    $("#dislikeBtn<?php echo $video['id']; ?>").addClass("myVote");
                                                }
                                                $("#likeBtn<?php echo $video['id']; ?> small").text(response.likes);
                                                $("#dislikeBtn<?php echo $video['id']; ?> small").text(response.dislikes);
                                            }
                                        });
                                        return false;
                                    });
            <?php
        } else {
            ?>
                                    $("#dislikeBtn<?php echo $video['id']; ?>, #likeBtn<?php echo $video['id']; ?>").click(function () {

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
                <div class="row bgWhite list-group-item" id="shareDiv<?php echo $video['id']; ?>">
                    <div class="tabbable-panel">
                        <div class="tabbable-line text-muted">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link " href="#tabShare<?php echo $video['id']; ?>" data-toggle="tab">
                                        <span class="fa fa-share"></span>
                                        <?php echo __("Share"); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " href="#tabEmbeded<?php echo $video['id']; ?>" data-toggle="tab">
                                        <span class="fa fa-code"></span>
                                        <?php echo __("Embeded"); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#tabEmail<?php echo $video['id']; ?>" data-toggle="tab">
                                        <span class="fa fa-envelope"></span>
                                        <?php echo __("E-mail"); ?>
                                    </a>
                                </li>
                            </ul>
                            <div class="tab-content clearfix">
                                <div class="tab-pane active" id="tabShare<?php echo $video['id']; ?>">
                                    <?php
                                    $url = urlencode($global['webSiteRootURL'] . "video/" . $video['clean_title']);
                                    $title = urlencode($video['title']);
                                    $facebookURL = "https://www.facebook.com/sharer.php?u={$url}&title={$title}";
                                    $twitterURL = "http://twitter.com/home?status={$title}+{$url}";
                                    $googleURL = "https://plus.google.com/share?url={$url}";
                                    ?>
                                    <ul class="social-network social-circle">
                                        <li><a href="<?php echo $facebookURL; ?>" target="_blank" class="icoFacebook" title="Facebook"><i class="fab fa-facebook-square"></i></a></li>
                                        <li><a href="<?php echo $twitterURL; ?>" target="_blank"  class="icoTwitter" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                        <li><a href="<?php echo $googleURL; ?>" target="_blank"  class="icoGoogle" title="Google +"><i class="fab fa-google-plus"></i></a></li>
                                    </ul>
                                </div>
                                <div class="tab-pane" id="tabEmbeded<?php echo $video['id']; ?>">
                                    <h4><span class="glyphicon glyphicon-share"></span> <?php echo __("Share Video"); ?>:</h4>
                                    <textarea class="form-control" style="min-width: 100%" rows="5"><?php
                                        if ($video['type'] == 'video') {
                                            $code = '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                        } else {
                                            $code = '<iframe width="350" height="40" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                        }
                                        echo htmlentities($code);
                                        ?></textarea>
                                </div>
                                <div class="tab-pane" id="tabEmail<?php echo $video['id']; ?>">
                                    <?php
                                    if (!User::isLogged()) {
                                        ?>
                                        <strong>
                                            <a href="<?php echo $global['webSiteRootURL']; ?>user"><?php echo __("Sign in now!"); ?></a>
                                        </strong>
                                        <?php
                                    } else {
                                        ?>
                                        <form class="well form-horizontal" action="<?php echo $global['webSiteRootURL']; ?>sendEmail" method="post"  id="contact_form<?php echo $video['id']; ?>">
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
                                                            <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo _("I would like to share this video with you:"); ?> <?php echo $global['webSiteRootURL']; ?>video/<?php echo $video['clean_title']; ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <label class="col-md-4 control-label"><?php echo __("Type the code"); ?></label>
                                                    <div class="col-md-8 inputGroupContainer">
                                                        <div class="input-group">
                                                            <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha" id="captcha<?php echo $video['id']; ?>"></span>
                                                            <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha<?php echo $video['id']; ?>"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                                            <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText<?php echo $video['id']; ?>">
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

                                                $('#btnReloadCapcha<?php echo $video['id']; ?>').click(function () {
                                                    $('#captcha<?php echo $video['id']; ?>').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                                                    $('#captchaText<?php echo $video['id']; ?>').val('');
                                                });
                                                $('#contact_form<?php echo $video['id']; ?>').submit(function (evt) {
                                                    evt.preventDefault();
                                                    modal.showPleaseWait();
                                                    $.ajax({
                                                        url: '<?php echo $global['webSiteRootURL']; ?>sendEmail',
                                                        data: $('#contact_form<?php echo $video['id']; ?>').serializeArray(),
                                                        type: 'post',
                                                        success: function (response) {
                                                            modal.hidePleaseWait();
                                                            if (!response.error) {
                                                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your message has been sent!"); ?>", "success");
                                                            } else {
                                                                swal("<?php echo __("Your message could not be sent!"); ?>", response.error, "error");
                                                            }
                                                            $('#btnReloadCapcha<?php echo $video['id']; ?>').trigger('click');
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

                <div class="row bgWhite list-group-item" id="commentDiv<?php echo $video['id']; ?>">
                    <div class="input-group">
                        <textarea class="form-control custom-control" rows="3" style="resize:none" id="comment<?php echo $video['id']; ?>" maxlength="200" <?php
                        if (!User::canComment()) {
                            echo "disabled";
                        }
                        ?>><?php
                                      if (!User::canComment()) {
                                          echo __("You cannot comment on videos");
                                      }
                                      ?></textarea>
                        <?php if (User::canComment()) { ?>
                            <span class="input-group-addon btn btn-success" id="saveCommentBtn<?php echo $video['id']; ?>" <?php
                            if (!User::canComment()) {
                                echo "disabled='disabled'";
                            }
                            ?>><span class="glyphicon glyphicon-comment"></span> <?php echo __("Comment"); ?></span>
                              <?php } else { ?>
                            <a class="input-group-addon btn btn-success" href="<?php echo $global['webSiteRootURL']; ?>user"><span class="glyphicon glyphicon-log-in"></span> <?php echo __("You must login to be able to comment on videos"); ?></a>
                        <?php } ?>
                    </div>
                    <div class="pull-right" id="count_message<?php echo $video['id']; ?>"></div>
                    <script>
                        $(document).ready(function () {
                            var text_max = 200;
                            $('#count_message<?php echo $video['id']; ?>').html(text_max + ' <?php echo __("remaining"); ?>');
                            $('#comment<?php echo $video['id']; ?>').keyup(function () {
                                var text_length = $(this).val().length;
                                var text_remaining = text_max - text_length;
                                $('#count_message<?php echo $video['id']; ?>').html(text_remaining + ' <?php echo __("remaining"); ?>');
                            });
                        });
                    </script>
                    <h4><?php echo __("Comments"); ?>:</h4>
                    <table id="grid<?php echo $video['id']; ?>" class="table table-condensed table-hover table-striped nowrapCell">
                        <thead>
                            <tr>
                                <th data-column-id="comment" ><?php echo __("Comment"); ?></th>
                            </tr>
                        </thead>
                    </table>

                    <script>
                        $(document).ready(function () {
                            var grid = $("#grid<?php echo $video['id']; ?>").bootgrid({
                                labels: {
                                    noResults: "<?php echo __("No results found!"); ?>",
                                    all: "<?php echo __("All"); ?>",
                                    infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                                    loading: "<?php echo __("Loading..."); ?>",
                                    refresh: "<?php echo __("Refresh"); ?>",
                                    search: "<?php echo __("Search"); ?>",
                                },
                                ajax: true,
                                url: "<?php echo $global['webSiteRootURL'] . "comments.json/" . $video['id']; ?>",
                                sorting: false,
                                templates: {
                                    header: ""
                                }
                            });
                            $('#saveCommentBtn<?php echo $video['id']; ?>').click(function () {
                                if ($(this).attr('disabled') === 'disabled') {
                                    return false;
                                }
                                if ($('#comment<?php echo $video['id']; ?>').val().length > 5) {
                                    modal.showPleaseWait();
                                    $.ajax({
                                        url: '<?php echo $global['webSiteRootURL']; ?>objects/commentAddNew.json.php',
                                        method: 'POST',
                                        data: {'comment': $('#comment<?php echo $video['id']; ?>').val(), 'video': "<?php echo $video['id']; ?>"},
                                        success: function (response) {
                                            if (response.status === "1") {
                                                $('#comment<?php echo $video['id']; ?>').val('');
                                                $('#grid<?php echo $video['id']; ?>').bootgrid('reload');
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
                <!-- finish all stuffs -->
                <script>
                    var isLoaded<?php echo $video['id']; ?> = false;
                    $(document).ready(function () {

                        $("#shareDiv<?php echo $video['id']; ?>").slideUp();
                        $("#shareBtn<?php echo $video['id']; ?>").click(function () {
                            $("#shareDiv<?php echo $video['id']; ?>").slideToggle();
                            return false;
                        });

                        $("#commentDiv<?php echo $video['id']; ?>").slideUp();
                        $("#commentBtn<?php echo $video['id']; ?>").click(function () {
                            $("#commentDiv<?php echo $video['id']; ?>").slideToggle();
                            return false;
                        });

        <?php
        if ($count == $half) {
            ?>
                            $(window).scroll(function () {
                                if (isLoaded<?php echo $video['id']; ?>) {
                                    return false;
                                }
                                var $h1 = $("#mainVideo<?php echo $video['id']; ?>");
                                var window_offset = $h1.offset().top - $(window).scrollTop();
                                if (window_offset > 50 && window_offset < 100) {
                                    isLoaded<?php echo $video['id']; ?> = true;
                                    load(<?php echo $_GET['page'] + 1; ?>);
                                }
                            });

            <?php
        }
        if ($video['type'] === "video") {
            ?>
                            //Prevent HTML5 video from being downloaded (right-click saved)?
                            $('#mainVideo<?php echo $video['id']; ?>').bind('contextmenu', function () {
                                return false;
                            });
                            fullDuration<?php echo $video['id']; ?> = strToSeconds('<?php echo @$ad['duration']; ?>');
                            player<?php echo $video['id']; ?> = videojs('mainVideo<?php echo $video['id']; ?>');

                            player<?php echo $video['id']; ?>.zoomrotate(<?php echo $transformation; ?>);
                            player<?php echo $video['id']; ?>.ready(function () {

            <?php if (!empty($logId)) { ?>
                                    isPlayingAd<?php echo $video['id']; ?> = true;
                                    this.on('ended', function () {
                                        console.log("Finish Video");
                                        if (isPlayingAd<?php echo $video['id']; ?>) {
                                            isPlayingAd<?php echo $video['id']; ?> = false;
                                            $('#adButton<?php echo $video['id']; ?>').trigger("click");
                                        }

                                    });
                                    this.on('timeupdate', function () {
                                        var durationLeft = fullDuration<?php echo $video['id']; ?> - this.currentTime();
                                        $("#adUrl<?php echo $video['id']; ?> .time").text(secondsToStr(durationLeft + 1, 2));
                <?php if (!empty($ad['skip_after_seconds'])) {
                    ?>
                                            if (isPlayingAd<?php echo $video['id']; ?> && this.currentTime() ><?php echo intval($ad['skip_after_seconds']); ?>) {
                                                $('#adButton<?php echo $video['id']; ?>').fadeIn();
                                            }
                <?php }
                ?>
                                    });
            <?php } else {
                ?>
                                    this.on('ended', function () {
                                        console.log("Finish Video");
                                    });
            <?php }
            ?>
                            });
                            player<?php echo $video['id']; ?>.persistvolume({
                                namespace: "YouPHPTube"
                            });
            <?php if (!empty($logId)) { ?>
                                $('#adButton<?php echo $video['id']; ?>').click(function () {
                                    console.log("Change Video");
                                    fullDuration<?php echo $video['id']; ?> = strToSeconds('<?php echo $video['duration']; ?>');
                                    changeVideoSrc(player<?php echo $video['id']; ?>, "<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>");
                                                $('#mainVideo<?php echo $video['id']; ?>').parent().removeClass("ad");
                                                return false;
                                            });
            <?php }
        }
        ?>

                                });
                </script>
            </div>
        </div>
        <?php
    }
} else {
    ?>
    <div class="alert alert-warning">
        <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Warning"); ?>!</strong> <?php echo __("We have not found any videos or audios to show"); ?>.
    </div>
<?php } ?>
