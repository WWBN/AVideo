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
require_once $global['systemRootPath'] . 'objects/subscribe.php';

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
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/FaceBookTube/view/style.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/FaceBookTube/view/player.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/video.js/video.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-rotatezoom/videojs.zoomrotate.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/videojs-persistvolume/videojs.persistvolume.js" type="text/javascript"></script>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>
        <div class="row text-center" style="padding: 10px;">
            <?php
            echo $config->getAdsense();
            ?>
        </div>
        <div class="container-fluid gallery" itemscope itemtype="http://schema.org/VideoObject">
            <div class="col-lg-2 col-md-1 col-sm-1 hidden-xs"></div>
            <div class="col-lg-6 col-md-7 col-sm-8 col-xs-12">
                <?php
                if (!empty($videos)) {
                    foreach ($videos as $video) {
                        $ad = Video_ad::getAdFromCategory($video['categories_id']);
                        $subscribe = Subscribe::getButton($video['users_id']);
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
                        ?>
                        <div class="row fbRow">
                            <div class="col-md-10 col-md-offset-1 list-group-item">
                                <div><?php echo $video['creator']; ?> </div>
                                <div class="main-video embed-responsive <?php
                                echo $embedResponsiveClass;
                                if (!empty($logId)) {
                                    echo " ad";
                                }
                                ?>">
                                    <video poster="<?php echo $poster; ?>" controls crossorigin 
                                           class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" 
                                           id="mainVideo<?php echo $video['id']; ?>"  data-setup='{ "aspectRatio": "<?php echo $aspectRatio; ?>" }'>
                                        <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $playNowVideo['filename']; ?>.mp4" type="video/mp4">
                                        <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $playNowVideo['filename']; ?>.webm" type="video/webm">
                                        <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                                        <p class="vjs-no-js">
                                            <?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                                            <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                                        </p>
                                    </video>
                                    <?php if (!empty($logId)) { ?>
                                        <div id="adUrl<?php echo $video['id']; ?>" class="adControl" ><?php echo __("Ad"); ?> <span class="time">0:00</span> <i class="fa fa-info-circle"></i>
                                            <a href="<?php echo $global['webSiteRootURL']; ?>adClickLog?video_ads_logs_id=<?php echo $logId; ?>&adId=<?php echo $ad['id']; ?>" target="_blank" ><?php
                                                $url = parse_url($ad['redirect']);
                                                echo $url['host'];
                                                ?> <i class="fa fa-external-link"></i>
                                            </a>
                                        </div>
                                        <a id="adButton<?php echo $video['id']; ?>" href="#" class="adControl" <?php if (!empty($ad['skip_after_seconds'])) { ?> style="display: none;" <?php } ?>><?php echo __("Skip Ad"); ?> <span class="fa fa-step-forward"></span></a>
                                    <?php } ?>
                                </div>
                                <script>

                                    $(document).ready(function () {
                                        $(window).scroll(function () {
                                            $(".fbRow").each(function (index) {
                                                var $h1 = $(this);
                                                var window_offset = $h1.offset().top - $(window).scrollTop();
                                                if (window_offset > 50 && window_offset < 100) {
                                                    $(".fbRow").each(function (index) {
                                                        $(this).find('video').get(0).pause();
                                                    });
                                                    $(this).find('video').get(0).play();
                                                    return true;
                                                }
                                            });
                                        });
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
        <?php } ?>
                                                });
                                </script>
                                <?php
                                echo $subscribe;
                                ?>
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
            </div>
            <div class="col-lg-4 col-md-4 col-sm-3 hidden-xs">
                <div data-spy="affix" style="margin-right: 10vw;" >
                    <div class="list-group-item ">
                        <?php
                        echo $config->getAdsense();
                        ?>
                    </div>                    
                </div>
            </div>


        </div>
        <?php
        include 'include/footer.php';
        ?>


    </body>
</html>
