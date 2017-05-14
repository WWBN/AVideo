<?php
if (!file_exists('../videos/configuration.php')) {
    if (!file_exists('../install/index.php')) {
        die("No Configuration and no Installation");
    }
    header("Location: install/index.php");
}

require_once '../videos/configuration.php';

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
require_once $global['systemRootPath'] . 'objects/video_statistic.php';
$video = Video::getVideo("", "viewableNotAd");
if (!empty($video)) {
    $ad = Video_ad::getAdFromCategory($video['categories_id']);
    VideoStatistic::save($video['id']);
    $name = empty($video['name']) ? substr($video['user'], 0, 5) . "..." : $video['name'];
    $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
    $obj = new Video("", "", $video['id']);
    // dont need because have one embeded video on this page
    //$resp = $obj->addView();
}

if (empty($_GET['page'])) {
    $_GET['page'] = 1;
} else {
    $_GET['page'] = intval($_GET['page']);
}
$_POST['rowCount'] = 10;
$_POST['current'] = $_GET['page'];
$_POST['sort']['created'] = 'desc';
$videos = Video::getAllVideos("viewableNotAd");
foreach ($videos as $key => $value) {
    $name = empty($value['name']) ? $value['user'] : $value['name'];
    $videos[$key]['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($value['users_id']) . '" alt="" class="img img-responsive img-circle" style="max-width: 20px;"/></div><div class="commentDetails" style="margin-left:25px;"><div class="commenterName"><strong>' . $name . '</strong> <small>' . humanTiming(strtotime($value['videoCreation'])) . '</small></div></div>';
}
$total = Video::getTotalVideos("viewableNotAd");
$totalPages = ceil($total / $_POST['rowCount']);
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

if ($video['type'] !== "audio") {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
} else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $video['title']; ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - Make your own tube sitetags " />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/social.css" rel="stylesheet" type="text/css"/>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="container-fluid" itemscope itemtype="http://schema.org/VideoObject">

            <?php
            if (!empty($video)) {
                if (empty($_GET['search'])) {
                    if (empty($video['type']) || file_exists("{$global['systemRootPath']}videos/{$video['filename']}.mp4")) {
                        $video['type'] = "video";
                    }
                    ?>
                    <?php
                    require "{$global['systemRootPath']}view/include/{$video['type']}.php";
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 ">
                            <div class="row bgWhite">
                                <div class="row divMainVideo">
                                    <div class="col-xs-4 col-sm-4 col-lg-4">
                                        <img src="<?php echo $poster; ?>" alt="<?php echo $video['title']; ?>" class="img img-responsive" height="130px" itemprop="thumbnail" /> 
                                        <span class="duration" itemprop="duration"><?php echo Video::getCleanDuration($video['duration']); ?></span>
                                        <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                        <meta itemprop="contentURL" content="<?php echo $global['webSiteRootURL'], "video/", $video['clean_title']; ?>" />
                                        <meta itemprop="embedURL" content="<?php echo $global['webSiteRootURL'], "videoEmbeded/", $video['clean_title']; ?>" />
                                        <meta itemprop="uploadDate" content="<?php echo $video['created']; ?>" />
                                    </div>
                                    <div class="col-xs-8 col-sm-8 col-lg-8">
                                        <h1 itemprop="name">
                                            <?php echo $video['title']; ?>
                                            <small>
                                                <?php
                                                $video['tags'] = Video::getTags($video['id']);
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
                                        <div class="col-xs-12 col-sm-12 col-lg-12"><?php echo $video['creator']; ?></div>
                                        <span class="watch-view-count pull-right" itemprop="interactionCount"><?php echo number_format($video['views_count'], 0); ?> <?php echo __("Views"); ?></span>
                                    </div> 
                                </div>

                                <div class="row">
                                    <div class="col-md-12 col-lg-12 watch8-action-buttons">
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
                                                    $("#dislikeBtn, #likeBtn").tooltip();
            <?php
        }
        ?>

                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>

                            <div class="row bgWhite" id="shareDiv">
                                <div class="tabbable-panel">
                                    <div class="tabbable-line">
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
                                                $url = urlencode($global['webSiteRootURL'] . "video/" . $video['clean_title']);
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

                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                           value="<?php
                                                           if ($video['type'] == 'video') {
                                                               $code = '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                           } else {
                                                               $code = '<iframe width="350" height="40" style="max-width: 100%;max-height: 100%;" src="' . $global['webSiteRootURL'] . 'videoEmbeded/' . $video['clean_title'] . '" frameborder="0" allowfullscreen="allowfullscreen" class="YouPHPTubeIframe"></iframe>';
                                                           }
                                                           echo htmlentities($code);
                                                           ?>" placeholder="<?php echo __("Share Video"); ?>" id="code-input">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-default" type="button" id="code-button"
                                                                data-toggle="tooltip" data-placement="button"
                                                                title="<?php echo __("Preview"); ?>">
                                                            <span class="fa fa-eye"></span>    <?php echo __("Preview"); ?>
                                                        </button>
                                                    </span>
                                                    <script>
                                                        $(document).ready(function () {
                                                            $("#showMore").slideUp();
                                                            $("#code-button").click(function () {
                                                                $("#showMore").slideToggle();
                                                                return false;
                                                            });
                                                        });
                                                    </script>
                                                </div>
                                                <div class="row" id="showMore">
                                                    <?php echo $code; ?>
                                                </div> 
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
                                                                        <textarea class="form-control" name="comment" placeholder="<?php echo __("Message"); ?>"><?php echo _("I would like to share this video with you:"); ?> <?php echo $global['webSiteRootURL']; ?>video/<?php echo $video['clean_title']; ?></textarea>
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
                            <div class="row bgWhite">
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
                            <div class="row bgWhite">
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
                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 bgWhite">
                            <div class="col-lg-12 col-sm-12 col-xs-12">
                                <?php
                                echo $config->getAdsense();
                                ?>
                            </div>
                            <?php
                            foreach ($videos as $value) {
                                if ($video['id'] == $value['id']) {
                                    continue; // skip video
                                }
                                ?>
                                <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border" itemscope itemtype="http://schema.org/VideoObject">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>" class="videoLink">
                                        <div class="col-lg-5 col-sm-5 col-xs-5 nopadding">
                                            <?php
                                            if ($value['type'] !== "audio") {
                                                $img = "{$global['webSiteRootURL']}videos/{$value['filename']}.jpg";
                                            } else {
                                                $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
                                            }
                                            ?>
                                            <img src="<?php echo $img; ?>" alt="<?php echo $value['title']; ?>" class="img-responsive" height="130px" itemprop="thumbnail" />

                                            <meta itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                            <meta itemprop="contentURL" content="<?php echo $global['webSiteRootURL'], "video/", $value['clean_title']; ?>" />
                                            <meta itemprop="embedURL" content="<?php echo $global['webSiteRootURL'], "videoEmbeded/", $value['clean_title']; ?>" />
                                            <meta itemprop="uploadDate" content="<?php echo $value['created']; ?>" />

                                            <span class="glyphicon glyphicon-play-circle"></span>
                                            <span class="duration" itemprop="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                        </div>
                                        <div class="col-lg-7 col-sm-7 col-xs-7 videosDetails">
                                            <div class="text-uppercase row"><strong itemprop="name" class="title"><?php echo $value['title']; ?></strong></div>
                                            <div class="details row" itemprop="description">
                                                <div>
                                                    <strong><?php echo __("Category"); ?>: </strong>
                                                    <span class="<?php echo $value['iconClass']; ?>"></span> 
                                                    <?php echo $value['category']; ?>
                                                </div>
                                                <div>
                                                    <strong class=""><?php echo number_format($value['views_count'], 0); ?></strong> <?php echo __("Views"); ?>
                                                </div>
                                                <div><strong><?php echo $value['creator']; ?></strong></div>

                                            </div>
                                            <div class="row">
                                                <?php
                                                foreach ($value['tags'] as $value2) {
                                                    if ($value2->label === __("Group")) {
                                                        ?>
                                                        <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                                                        <?php
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
                            <ul class="pages">
                            </ul>
                            <script>
                                $(document).ready(function () {
                                    // Total Itens <?php echo $total; ?>

                                    $('.pages').bootpag({
                                        total: <?php echo $totalPages; ?>,
                                        page: <?php echo $_GET['page']; ?>,
                                        maxVisible: 10
                                    }).on('page', function (event, num) {
                                        window.location.replace("<?php echo $global['webSiteRootURL']; ?>page/" + num);
                                    });
                                });
                            </script>
                        </div>

                        <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-lg-1"></div>
                        <div class="col-xs-12 col-sm-12 col-lg-10">
                            <?php
                            foreach ($videos as $value) {
                                ?>
                                <div class="col-lg-3 col-sm-12 col-xs-12">
                                    <a href="<?php echo $global['webSiteRootURL']; ?>video/<?php echo $value['clean_title']; ?>" title="<?php echo $value['title']; ?>">
                                        <img src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $value['filename']; ?>.jpg" alt="<?php echo $value['title']; ?>" class="img-responsive" height="130px" />
                                        <h2><?php echo $value['title']; ?></h2>
                                        <span class="glyphicon glyphicon-play-circle"></span>
                                        <span class="duration"><?php echo Video::getCleanDuration($value['duration']); ?></span>
                                    </a>
                                </div>
                                <?php
                            }
                            ?> 
                            <ul class="pages">
                            </ul>
                            <script>
                                $(document).ready(function () {
                                    // Total Itens <?php echo $total; ?>

                                    $('.pages').bootpag({
                                        total: <?php echo $totalPages; ?>,
                                        page: <?php echo $_GET['page']; ?>,
                                        maxVisible: 10
                                    }).on('page', function (event, num) {
                                        window.location.replace("<?php echo $global['webSiteRootURL']; ?>page/" + num);
                                    });
                                });
                            </script>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-lg-1"></div>
                    </div>
                    <?php
                }
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


    </body>
</html>
