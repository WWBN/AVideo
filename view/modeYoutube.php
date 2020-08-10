<?php
$modeYouTubeTime = microtime(true);
$modeYouTubeTimeLog = array();
global $global, $config;
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
$isModeYouTube = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
//_error_log("modeYoutube: session_id = " . session_id() . " IP = " . getRealIpAddr());

if (!empty($_GET['evideo'])) {
    $v = Video::decodeEvideo();
    $evideo = $v['evideo'];
}
if (!empty($evideo)) {
    $video = $v['video'];
    $img = $evideo->thumbnails;
    $poster = $evideo->thumbnails;
    $imgw = 1280;
    $imgh = 720;
    $autoPlaySources = array();
    $autoPlayURL = '';
    $autoPlayPoster = '';
    $autoPlayThumbsSprit = '';
} else {
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

    $modeYouTubeTimeLog['Code part 1'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    if (!empty($_GET['playlist_id'])) {
        
        if(preg_match("/^[0-9]+$/", $_GET['playlist_id'])){
            $playlist_id = $_GET['playlist_id'];
        }else if(User::isLogged()){
            if($_GET['playlist_id'] == "favorite"){
                $playlist_id = PlayList::getFavoriteIdFromUser(User::getId());
            }else{
                $playlist_id = PlayList::getWatchLaterIdFromUser(User::getId());
            }
        }
        
        if (!empty($_GET['playlist_index'])) {
            $playlist_index = $_GET['playlist_index'];
        } else {
            $playlist_index = 0;
        }

        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlist_id);
        $videosPlayList = Video::getAllVideos("viewable", false, false, $videosArrayId, false, true);
        $videosPlayList = PlayList::sortVideos($videosPlayList, $videosArrayId);

        $videoSerie = Video::getVideoFromSeriePlayListsId($playlist_id);

        unset($_GET['playlist_id']);
        $isPlayListTrailer = false;
        if (!empty($videoSerie)) {
            $videoSerie = Video::getVideo($videoSerie["id"], "", true);
            if (!empty($videoSerie["trailer1"]) && filter_var($videoSerie["trailer1"], FILTER_VALIDATE_URL) !== FALSE) {
                $videoSerie["type"] = "embed";
                $videoSerie["videoLink"] = $videoSerie["trailer1"];
                array_unshift($videosPlayList, $videoSerie);
                array_unshift($videosArrayId, $videoSerie['id']);
                $isPlayListTrailer = true;
            }
        }
        if (empty($playlist_index) && $isPlayListTrailer) {
            $video = $videoSerie;
        } else {
            $vid = new Video("", "", $videosPlayList[$playlist_index]['id']);
            $_GET['videoName'] = $vid->getClean_title();
            $video = Video::getVideo($videosPlayList[$playlist_index]['id'], "viewable", false, false, false, true);
        }

        if (!empty($videosPlayList[$playlist_index + 1])) {
            $autoPlayVideo = Video::getVideo($videosPlayList[$playlist_index + 1]['id'], "viewableNotUnlisted", false, false, false, true);
            $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/" . ($playlist_index + 1);
        } else if (!empty($videosPlayList[0])) {
            $autoPlayVideo = Video::getVideo($videosPlayList[0]['id'], "viewableNotUnlisted", false, false, false, true);
            $autoPlayVideo['url'] = $global['webSiteRootURL'] . "playlist/{$playlist_id}/0";
        }
    } else {
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
            $video = AVideoPlugin::getVideo();
        }
        
        if(!empty($_GET['v']) && $video['id']!=$_GET['v']){
            $video = false;
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

        $modeYouTubeTimeLog['Code part 1.1'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        if (!empty($video['next_videos_id'])) {
            $modeYouTubeTimeLog['Code part 1.2'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            $autoPlayVideo = Video::getVideo($video['next_videos_id']);
        } else {
            $modeYouTubeTimeLog['Code part 1.3'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            /*
              if ($video['category_order'] == 1) {
              $modeYouTubeTimeLog['Code part 1.4'] = microtime(true)-$modeYouTubeTime;
              $modeYouTubeTime = microtime(true);
              unset($_POST['sort']);
              $category = Category::getAllCategories();
              $_POST['sort']['title'] = "ASC";

              $modeYouTubeTimeLog['Code part 1.4.1'] = microtime(true)-$modeYouTubeTime;
              $modeYouTubeTime = microtime(true);
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
             * 
             */
            $modeYouTubeTimeLog['Code part 1.5'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            $autoPlayVideo = Video::getRandom($video['id']);
            //}
        }

        $modeYouTubeTimeLog['Code part 1.6'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        if (!empty($autoPlayVideo)) {

            $name2 = User::getNameIdentificationById($autoPlayVideo['users_id']) . ' ' . User::getEmailVerifiedIcon($autoPlayVideo['users_id']);
            $autoPlayVideo['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($autoPlayVideo['users_id']) . '" alt="User Photo" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName"><strong>' . $name2 . '</strong> <small>' . humanTiming(strtotime($autoPlayVideo['videoCreation'])) . '</small></div></div>';
            $autoPlayVideo['tags'] = Video::getTags($autoPlayVideo['id']);
//$autoPlayVideo['url'] = $global['webSiteRootURL'] . $catLink . "video/" . $autoPlayVideo['clean_title'];
            $autoPlayVideo['url'] = Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], false, $get);
        }
    }
    $modeYouTubeTimeLog['Code part 2'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    if (!empty($video)) {
        $name = User::getNameIdentificationById($video['users_id']);
        $name = "<a href='" . User::getChannelLink($video['users_id']) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($video['users_id']) . "</a>";
        $subscribe = Subscribe::getButton($video['users_id']);
        $video['creator'] = '<div class="pull-left"><img src="' . User::getPhoto($video['users_id']) . '" alt="User Photo" class="img img-responsive img-circle zoom" style="max-width: 40px;"/></div><div class="commentDetails" style="margin-left:45px;"><div class="commenterName text-muted"><strong>' . $name . '</strong><br />' . $subscribe . '<br /><small>' . humanTiming(strtotime($video['videoCreation'])) . '</small></div></div>';
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
        } else if ($video['type'] === 'zip') {
            $type = 'zip';
        } else if ($video['type'] === 'article') {
            $type = 'article';
        }
        $images = Video::getImageFromFilename($video['filename'], $type);
        $poster = isMobile()?$images->thumbsJpg:$images->poster;
        if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
            $img = $images->posterPortrait;
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } else {
            $img = isMobile()?$images->thumbsJpg:$images->poster;
        }
    } else {
        $poster = "{$global['webSiteRootURL']}view/img/notfound.jpg";
    }
    $objSecure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
    $modeYouTubeTimeLog['Code part 3'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
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

    $modeYouTubeTimeLog['Code part 4'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    AVideoPlugin::getModeYouTube($v['id']);
    $modeYouTubeTimeLog['Code part 5'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    if (empty($video)) {
        header('HTTP/1.0 404 Not Found', true, 404);
    }
    $modeYouTubeTimeLog['Code part 6'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
}

// video not found
if (empty($video)) {
    $img = "{$global['webSiteRootURL']}view/img/this-video-is-not-available.jpg";
    $poster = "{$global['webSiteRootURL']}view/img/this-video-is-not-available.jpg";
    $imgw = 1280;
    $imgh = 720;
    unset($_SESSION['type']);
    session_write_close();
    $video = array();    
    $video['id'] = 0;
    $video['type'] = 'notfound';
    $video['rotation'] = 0;
    $video['videoLink'] = "";
    $video['title'] = __("Video Not Available");
    $video['clean_title'] = "video-not-available";
    $video['description'] = "";
    $video['duration'] = "";
    $video['creator'] = "";
    $video['likes'] = "";
    $video['dislikes'] = "";
    $video['category'] = "embed";
    $video['views_count'] = 0;
    $video['filename'] = "";
    
    header('HTTP/1.0 404 Not Found', true, 404);
}
$metaDescription = " {$video['id']}";

// make sure the title tag does not have more then 70 chars
$titleTag = "{$video['title']}";
if(strlen($titleTag)>50){
    $titleTag = substr($titleTag, 0, 50);
}else{
    $titleTag .= " - ".$config->getWebSiteTitle();
}
$titleTag = substr($titleTag, 0, 60);
$titleTag .= " - ".getSEOComplement();
$titleTag = substr($titleTag, 0, 70);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $titleTag; ?></title>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/player.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/css/social.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>
<?php
include $global['systemRootPath'] . 'view/include/head.php';
getOpenGraph(0);
getLdJson(0);
$modeYouTubeTimeLog['After head'] = microtime(true) - $modeYouTubeTime;
$modeYouTubeTime = microtime(true);
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
                    <div class="col-lg-12 col-sm-12 col-xs-12 AdsLeaderBoard AdsLeaderBoardTop">
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
    if (!in_array($vType, Video::$typeOptions)) {
        $vType = 'video';
    }
    require "{$global['systemRootPath']}view/include/{$vType}.php";
    $modeYouTubeTimeLog['After include video ' . $vType] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    ?>
                <div class="row" id="modeYoutubeBottom" style="margin: 0;">
                    <div class="row">
                        <div class="col-sm-1 col-md-1"></div>
                        <div class="col-sm-10 col-md-10 AdsLeaderBoard AdsLeaderBoardTop2">
                            <center style="margin:5px;">
    <?php echo getAdsLeaderBoardTop2(); ?>
                            </center>
                        </div>
                    </div>

                    <div class="col-sm-1 col-md-1"></div>
                    <div class="col-sm-6 col-md-6" id="modeYoutubeBottomContent">
    <?php
    require "{$global['systemRootPath']}view/modeYoutubeBottom.php";
    $modeYouTubeTimeLog['After include bottom '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    ?>
                    </div>
                    <div class="col-sm-4 col-md-4 bgWhite list-group-item rightBar" id="yptRightBar">
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
                                        <span itemprop="thumbnailUrl" content="<?php echo $img; ?>" />
                                        <span itemprop="contentURL" content="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title']); ?>" />
                                        <span itemprop="embedURL" content="<?php echo Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], true); ?>" />
                                        <span itemprop="uploadDate" content="<?php echo $autoPlayVideo['created']; ?>" />
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
                if(is_array($autoPlayVideo2)){
                    $autoPlayVideo2 = (object)$autoPlayVideo2;
                }
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


    $modeYouTubeTimeLog['After autoplay and playlist '] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    ?>
                        <div class="col-lg-12 col-sm-12 col-xs-12 extraVideos nopadding"></div>
                        <!-- videos List -->
                        <div id="videosList">
    <?php
    if (empty($playlist_id)) {
        include $global['systemRootPath'] . 'view/videosList.php';
    }
    ?>
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

                                if (typeof Cookies.get('autoplay') === 'undefined') {
    <?php if ($config->getAutoplay()) { ?>
                                        $("#autoplay").prop('checked', true);
                                        Cookies.set('autoplay', true, {
                                            path: '/',
                                            expires: 365
                                        });
        <?php
    }
    ?>
                                }

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
            <?php
            $modeYouTubeTimeLog['before add js '] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video.min.js" type="text/javascript"></script>
        <?php
        echo AVideoPlugin::afterVideoJS();
        if ($advancedCustom != false) {
            $disableYoutubeIntegration = $advancedCustom->disableYoutubePlayerIntegration;
        } else {
            $disableYoutubeIntegration = false;
        }

        if ((isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false)) {
            if ($_GET['isEmbedded'] == "y") {
                ?>
                <script src="<?php echo $global['webSiteRootURL']; ?>view/js/videojs-youtube/Youtube.js" type="text/javascript"></script>
                <?php
            } else if ($_GET['isEmbedded'] == "v") {
                ?>
                <script src="<?php echo $global['webSiteRootURL']; ?>view/js/videojs-vimeo/videojs-vimeo.js" type="text/javascript"></script>
                <?php
            }
        }
        include $global['systemRootPath'] . 'view/include/footer.php';
        $videoJSArray = array(
            "view/js/videojs-persistvolume/videojs.persistvolume.js",
            "view/js/BootstrapMenu.min.js");
        $jsURL = combineFiles($videoJSArray, "js");

        $modeYouTubeTimeLog['after add js and footer '] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        echo "<!-- \n";
        foreach ($modeYouTubeTimeLog as $key => $value) {
            if ($value > 0.5) {
                echo "*** ";
            }
            echo "{$key} = {$value} seconds \n";
        }
        echo "\n -->";
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
