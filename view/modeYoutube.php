<?php
global $global, $config;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/subscribe.php';
require_once $global['systemRootPath'] . 'objects/functions.php';

$img = "{$global['webSiteRootURL']}view/img/notfound.jpg";
$poster = "{$global['webSiteRootURL']}view/img/notfound.jpg";
$imgw = 1280;
$imgh = 720;

if (!empty($_GET['type'])) {
    if ($_GET['type'] == 'audio') {
        $_SESSION['type'] = 'audio';
    } else
    if ($_GET['type'] == 'video') {
        $_SESSION['type'] = 'video';
    } else
    if ($_GET['type'] == 'pdf') {
        $_SESSION['type'] = 'pdf';
    } else {
        $_SESSION['type'] = "";
        unset($_SESSION['type']);
    }
} else {
    unset($_SESSION['type']);
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/video.php';

$catLink = "";
if (!empty($_GET['catName'])) {
    $catLink = "cat/{$_GET['catName']}/";
}

// add this because if you change the video category the video was not loading anymore
$catName = @$_GET['catName'];

if (empty($_GET['clean_title']) && (isset($advancedCustom->forceCategory) && $advancedCustom->forceCategory === false)) {
    $_GET['catName'] = "";
}

if (empty($video)) {
    $video = Video::getVideo("", "viewable", false, false, true, true);
}

if (empty($video)) {
    $video = Video::getVideo("", "viewable", false, false, false, true);
}
if (empty($video)) {
    $video = YouPHPTubePlugin::getVideo();
}

// allow users to count a view again in case it is refreshed
Video::unsetAddView($video['id']);

// add this because if you change the video category the video was not loading anymore
$_GET['catName'] = $catName;

$_GET['isMediaPlaySite'] = $video['id'];
$obj = new Video("", "", $video['id']);

/*
  if (empty($_SESSION['type'])) {
  $_SESSION['type'] = $video['type'];
  }
 * 
 */
// $resp = $obj->addView();

$get = array('channelName' => @$_GET['channelName'], 'catName' => @$_GET['catName']);

if (!empty($_GET['playlist_id'])) {
    $playlist_id = $_GET['playlist_id'];
    if (!empty($_GET['playlist_index'])) {
        $playlist_index = $_GET['playlist_index'];
    } else {
        $playlist_index = 0;
    }

    $videosArrayId = PlayList::getVideosIdFromPlaylist($_GET['playlist_id']);
    $videosPlayList = Video::getAllVideos("viewable",false, false, $videosArrayId, false, true);
    $videosPlayList = PlayList::sortVideos($videosPlayList, $videosArrayId);
    
    $video = Video::getVideo($videosPlayList[$playlist_index]['id'], "viewable", false, false, false, true);
    if (!empty($videosPlayList[$playlist_index + 1])) {
        $autoPlayVideo = Video::getVideo($videosPlayList[$playlist_index + 1]['id'], "viewable", false, false, false, true);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/" . ($playlist_index + 1);
    } else if (!empty($videosPlayList[0])) {
        $autoPlayVideo = Video::getVideo($videosPlayList[0]['id'], "viewable", false, false, false, true);
        $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/0";
    }
   
    unset($_GET['playlist_id']);
} else {
    if (!empty($video['next_videos_id'])) {
        $autoPlayVideo = Video::getVideo($video['next_videos_id']);
    } else {
        if ($video['category_order'] == 1) {
            unset($_POST['sort']);
            $category = Category::getAllCategories();
            $_POST['sort']['title'] = "ASC";

            // maybe there's a more slim method?
            $videos = Video::getAllVideos();
            $videoFound = false;
            $autoPlayVideo;
            foreach ($videos as $value) {
                if ($videoFound) {
                    $autoPlayVideo = $value;
                    break;
                }

                if ($value['id'] == $video['id']) {
                    // if the video is found, make another round to have the next video properly.
                    $videoFound = true;
                }
            }
        } else {
            $autoPlayVideo = Video::getRandom($video['id']);
        }
    }

    if (!empty($autoPlayVideo)) {

        $name2 = User::getNameIdentificationById($autoPlayVideo['users_id']);
        $autoPlayVideo['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($autoPlayVideo['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName"><strong>' . $name2 . '</strong> <small>' . humanTiming(strtotime($autoPlayVideo['videoCreation'])) . '</small></div></div>';
        $autoPlayVideo['tags'] = Video::getTags($autoPlayVideo['id']);
        //$autoPlayVideo['url'] = $global['webSiteRootURL'] . $catLink . "video/" . $autoPlayVideo['clean_title'];
        $autoPlayVideo['url'] = Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], false, $get);
    }
}

if (!empty($video)) {
    $name = User::getNameIdentificationById($video['users_id']);
    $name = "<a href='" . User::getChannelLink($video['users_id']) . "' class='btn btn-xs btn-default'>{$name}</a>";
    $subscribe = Subscribe::getButton($video['users_id']);
    $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br />' . $subscribe . '<br /><small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
    $obj = new Video("", "", $video['id']);

    // dont need because have one embeded video on this page
    // $resp = $obj->addView();
}

if ($video['type'] == "video") {
    $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
} else {
    $poster = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
}

if (!empty($video)) {
    $source = Video::getSourceFile($video['filename']);
    if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
        $img = $source['url'];
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else if ($video['type'] == "audio") {
        $img = "{$global['webSiteRootURL']}view/img/audio_wave.jpg";
    }
    $type = 'video';
    if ($video['type'] === 'pdf') {
        $type = 'pdf';
    }
    if ($video['type'] === 'article') {
        $type = 'article';
    }
    $images = Video::getImageFromFilename($video['filename'], $type);
    $poster = $images->poster;
    if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
        $img = $images->posterPortrait;
        $data = getimgsize($source['path']);
        $imgw = $data[0];
        $imgh = $data[1];
    } else {
        $img = $images->poster;
    }
} else {
    $poster = "{$global['webSiteRootURL']}view/img/notfound.jpg";
}
$objSecure = YouPHPTubePlugin::getObjectDataIfEnabled('SecureVideosDirectory');

if (!empty($autoPlayVideo)) {
    $autoPlaySources = getSources($autoPlayVideo['filename'], true);
    $autoPlayURL = $autoPlayVideo['url'];
    $autoPlayPoster = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
    $autoPlayThumbsSprit = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}_thumbsSprit.jpg";
} else {
    $autoPlaySources = array();
    $autoPlayURL = '';
    $autoPlayPoster = '';
    $autoPlayThumbsSprit = "";
}

if (empty($_GET['videoName'])) {
    $_GET['videoName'] = $video['clean_title'];
}

$v = Video::getVideoFromCleanTitle($_GET['videoName']);


YouPHPTubePlugin::getModeYouTube($v['id']);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $video['title']; ?> - <?php echo $config->getWebSiteTitle(); ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <?php 
        include $global['systemRootPath'] . 'view/include/head.php'; 
        getOpenGraph(0);
        getLdJson(0);
        ?>
        
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <?php
        if (!empty($advancedCustomUser->showChannelBannerOnModeYoutube)) {
            ?>
            <div class="container" style="margin-bottom: 10px;">
                <img src="<?php echo User::getBackground($video['users_id']); ?>" class="img img-responsive" />
            </div>
            <?php
        }
        ?>
        <div class="container-fluid principalContainer">
            <?php
            if (!empty($video)) {
                if (empty($video['type'])) {
                    $video['type'] = "video";
                }
                $img_portrait = ($video['rotation'] === "90" || $video['rotation'] === "270") ? "img-portrait" : "";
                ?>
                <div class="row">
                    <div class="col-lg-12 col-sm-12 col-xs-12">
                        <center style="margin:5px;">
                            <?php
                            $getAdsLeaderBoardTop = getAdsLeaderBoardTop();
                            if (!empty($getAdsLeaderBoardTop)) {
                                ?>
                                <style>
                                    .compress {
                                        top: 100px !important;
                                    }
                                </style>
                                <?php
                                echo $getAdsLeaderBoardTop;
                            }
                            ?>
                        </center>
                    </div>
                </div>
                <?php
                $vType = $video['type'];
                if ($vType == "linkVideo") {
                    $vType = "video";
                } else if ($vType == "live") {
                    $vType = "../../plugin/Live/view/liveVideo";
                } else if ($vType == "linkAudio") {
                    $vType = "audio";
                }
                require "{$global['systemRootPath']}view/include/{$vType}.php";
                ?>


                <div class="row" id="modeYoutubeBottom">
                    <div class="col-sm-1 col-md-1"></div>
                    <div class="col-sm-6 col-md-6" id="modeYoutubeBottomContent">
                        <?php
                        require "{$global['systemRootPath']}view/modeYoutubeBottom.php";
                        ?>
                    </div>
                    <div class="col-sm-4 col-md-4 bgWhite list-group-item rightBar">
                        <div class="col-lg-12 col-sm-12 col-xs-12 text-center">
                            <?php echo getAdsSideRectangle(); ?>
                        </div>
                        <?php
                        if (!empty($playlist_id)) {
                            include $global['systemRootPath'] . 'view/include/playlist.php';
                            ?>
                            <script>
                                $(document).ready(function () {
                                    Cookies.set('autoplay', true, {
                                        path: '/',
                                        expires: 365
                                    });
                                });
                            </script>
                        <?php } else if (empty($autoPlayVideo)) {
                            ?>
                            <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted" >
                                <strong><?php echo __("Autoplay ended"); ?></strong>
                                <span class="pull-right">
                                    <span><?php echo __("Autoplay"); ?></span>
                                    <span>
                                        <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                                    </span>
                                    <div class="material-switch pull-right">
                                        <input type="checkbox" class="saveCookie" name="autoplay" id="autoplay">
                                        <label for="autoplay" class="label-primary"></label>
                                    </div>
                                </span>
                            </div>
                        <?php } else if (!empty($autoPlayVideo)) { ?>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12 col-xs-12 autoplay text-muted">
                                    <strong><?php echo __("Up Next"); ?></strong>
                                    <span class="pull-right">
                                        <span><?php echo __("Autoplay"); ?></span>
                                        <span>
                                            <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="bottom"  title="<?php echo __("When autoplay is enabled, a suggested video will automatically play next."); ?>"></i>
                                        </span>
                                        <div class="material-switch pull-right">
                                            <input type="checkbox" class="saveCookie" name="autoplay" id="autoplay">
                                            <label for="autoplay" class="label-primary"></label>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            <div class="col-lg-12 col-sm-12 col-xs-12 bottom-border autoPlayVideo" id="autoPlayVideoDiv" itemscope itemtype="http://schema.org/VideoObject" >
                                <a href="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], "", $get); ?>" title="<?php echo str_replace('"', '', $autoPlayVideo['title']); ?>" class="videoLink h6">
                                    <div class="col-lg-5 col-sm-5 col-xs-5 nopadding thumbsImage">
                                        <?php
                                        $imgGif = "";
                                        if (file_exists("{$global['systemRootPath']}videos/{$autoPlayVideo['filename']}.gif")) {
                                            $imgGif = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.gif";
                                        }
                                        if ($autoPlayVideo['type'] === "pdf") {
                                            $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.png";
                                            $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                                        } else if (($autoPlayVideo['type'] !== "audio") && ($autoPlayVideo['type'] !== "linkAudio")) {
                                            $img = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
                                            $img_portrait = ($autoPlayVideo['rotation'] === "90" || $autoPlayVideo['rotation'] === "270") ? "img-portrait" : "";
                                        } else {
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
                        <?php } ?>
                        <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding"></div>
                        <!-- videos List -->
                        <div id="videosList">
                            <?php include $global['systemRootPath'] . 'view/videosList.php'; ?>
                        </div>
                        <!-- End of videos List -->

                        <script>
                            var fading = false;
                            var autoPlaySources = <?php echo json_encode($autoPlaySources); ?>;
                            var autoPlayURL = '<?php echo $autoPlayURL; ?>';
                            var autoPlayPoster = '<?php echo $autoPlayPoster; ?>';
                            var autoPlayThumbsSprit = '<?php echo $autoPlayThumbsSprit; ?>';

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
                                $("#autoplay").change(function () {
                                    showAutoPlayVideoDiv();
                                });
                                showAutoPlayVideoDiv();
                            });
                        </script>
                    </div>
                    <div class="col-sm-1 col-md-1"></div>
                </div>    

                <?php
            } else {
                ?>
                <br>
                <br>
                <br>
                <br>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span> <strong><?php echo __("Attention"); ?>!</strong> <?php echo empty($advancedCustom->videoNotFoundText->value) ? __("We have not found any videos or audios to show") : $advancedCustom->videoNotFoundText->value; ?>.
                </div>
            <?php } ?>
        </div>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script>
                        /*** Handle jQuery plugin naming conflict between jQuery UI and Bootstrap ***/
                        $.widget.bridge('uibutton', $.ui.button);
                        $.widget.bridge('uitooltip', $.ui.tooltip);
        </script>
        <?php
        $videoJSArray = array("view/js/video.js/video.js");
        if ($advancedCustom != false) {
            $disableYoutubeIntegration = $advancedCustom->disableYoutubePlayerIntegration;
        } else {
            $disableYoutubeIntegration = false;
        }

        if ((isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false)) {
            if ($_GET['isEmbedded'] == "y") {
                $videoJSArray[] = "view/js/videojs-youtube/Youtube.js";
            } else if ($_GET['isEmbedded'] == "v") {
                $videoJSArray[] = "view/js/videojs-vimeo/videojs-vimeo.js";
            }
        }
        $jsURL = combineFiles($videoJSArray, "js");
        ?>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        $videoJSArray = array(
            "view/js/videojs-persistvolume/videojs.persistvolume.js",
            "view/js/BootstrapMenu.min.js");
        $jsURL = combineFiles($videoJSArray, "js");
        ?>
        <script src="<?php echo $jsURL; ?>" type="text/javascript"></script>
        <script>
                        var fading = false;
                        var autoPlaySources = <?php echo json_encode($autoPlaySources); ?>;
                        var autoPlayURL = '<?php echo $autoPlayURL; ?>';
                        var autoPlayPoster = '<?php echo $autoPlayPoster; ?>';
                        var autoPlayThumbsSprit = '<?php echo $autoPlayThumbsSprit; ?>';

                        $(document).ready(function () {
                        });
        </script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
