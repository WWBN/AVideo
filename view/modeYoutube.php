<?php
//var_dump($_GET);exit;
global $global, $config, $isEmbed;
$modeYouTubeTime = microtime(true);
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
$isModeYouTube = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$TimeLogLimitMY = 0.5;
$timeLogNameMY = TimeLogStart("modeYoutube.php");
//_error_log("modeYoutube: session_id = " . session_id() . " IP = " . getRealIpAddr());
/*
if (useIframe() && !isIframe() && empty($_REQUEST['inMainIframe'])) {
    $paths = getIframePaths();
    //var_dump($paths);exit;
    header('Location: '.$paths['url']);
    exit;
}
 *
 */
//var_dump(__LINE__, __FILE__);exit;
if (!empty($_GET['evideo'])) {
    $v = Video::decodeEvideo();
    $evideo = $v['evideo'];
}

TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
$playlist_index = 0;
if (!empty($evideo)) {
    $video = $v['video'];
    $img = $evideo->thumbnails;
    $poster = $evideo->thumbnails;
    $imgw = 1280;
    $imgh = 720;
    $autoPlaySources = [];
    $autoPlayURL = '';
    $autoPlayPoster = '';
    $autoPlayThumbsSprit = '';
} else {
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    require_once $global['systemRootPath'] . 'objects/user.php';
    require_once $global['systemRootPath'] . 'objects/category.php';
    require_once $global['systemRootPath'] . 'objects/subscribe.php';
    require_once $global['systemRootPath'] . 'objects/functions.php';

    $img = "" . getURL("view/img/notfound.jpg");
    $poster = "" . getURL("view/img/notfound.jpg");
    $imgw = 1280;
    $imgh = 720;

    if (!empty($_GET['type'])) {
        if ($_GET['type'] == 'audio') {
            $_SESSION['type'] = 'audio';
        } elseif ($_GET['type'] == 'video') {
            $_SESSION['type'] = 'video';
        } elseif ($_GET['type'] == 'pdf') {
            $_SESSION['type'] = 'pdf';
        } else {
            $_SESSION['type'] = '';
            unset($_SESSION['type']);
        }
    } else {
        unset($_SESSION['type']);
    }
    session_write_close();

    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    if (empty($_GET['playlist_id']) && !empty($_GET['playlist_name'])) {
        $_GET['playlist_id'] = $_GET['playlist_name'];
    }else if (empty($_GET['playlist_id']) && !empty($_GET['playlists_id'])) {
        $_GET['playlist_id'] = $_GET['playlists_id'];
    }
    if (!empty($_GET['playlist_id'])) {
        $isSerie = 1;

        $plp = new PlayListPlayer(@$_GET['playlist_id'], @$_GET['playlists_tags_id']);

        $playListData = $plp->getPlayListData();

        $video = $plp->getCurrentVideo();
        if(!empty($video)){
            $_getVideos_id = intval($video['id']);
            $playlist_index = $plp->getIndex();
    
            if (empty($playListData)) {
                videoNotFound("Line code ".__LINE__);
            }
    
            $videosPlayList = $plp->getVideos();
            $autoPlayVideo = $plp->getNextVideo();
            $playlist_id = $plp->getPlaylists_id();
            //var_dump($video);exit;
        }
    } else {
        $catLink = '';
        if (!empty($_REQUEST['catName'])) {
            $catLink = "cat/{$_REQUEST['catName']}/";
        }

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        // add this because if you change the video category the video was not loading anymore
        $catName = @$_REQUEST['catName'];

        if (empty($_GET['clean_title']) && (isset($advancedCustom->forceCategory) && $advancedCustom->forceCategory === false)) {
            $_REQUEST['catName'] = '';
        }

        $videos_id = getVideos_id();
        if (empty($video) && !empty($videos_id)) {
            $video = Video::getVideo($videos_id, "viewable", false, false, false, true);
            //var_dump($_GET, $video);exit;
            //var_dump('Line: '.__LINE__, $_REQUEST['v'], $video);exit;
        }
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (empty($video)) {
            $video = Video::getVideo("", "viewable", false, false, true, true);
        }

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (empty($video)) {
            $video = Video::getVideo("", "viewable", false, false, false, true);
        }
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (empty($video)) {
            $video = AVideoPlugin::getVideo();
        }

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (!empty($_GET['v']) && (empty($video) || $video['id'] != $_GET['v'])) {
            $video = false;
        }
        if (!empty($video['id'])) {
            // allow users to count a view again in case it is refreshed
            Video::unsetAddView($video['id']);

            // add this because if you change the video category the video was not loading anymore
            $_REQUEST['catName'] = $catName;

            $_GET['isMediaPlaySite'] = $video['id'];
            $obj = new Video("", "", $video['id']);
        }

        $get = ['channelName' => @$_GET['channelName'], 'catName' => @$_REQUEST['catName']];

        $modeYouTubeTimeLog['Code part 1.1'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        if (!empty($video['next_videos_id'])) {
            $modeYouTubeTimeLog['Code part 1.2'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            $autoPlayVideo = Video::getVideo($video['next_videos_id']);
        } else {
            $modeYouTubeTimeLog['Code part 1.3'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            $modeYouTubeTimeLog['Code part 1.5'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            if (!empty($video['id'])) {
                $autoPlayVideo = Video::getRandom($video['id'], 'suggested');
                //var_dump($autoPlayVideo['id']);exit;
                if (empty($autoPlayVideo['id'])) {
                    $autoPlayVideo = Video::getRandom($video['id']);
                }
            }
            //}
        }

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        $modeYouTubeTimeLog['Code part 1.6'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        if (!empty($autoPlayVideo)) {
            $autoPlayVideo['creator'] = Video::getCreatorHTML($autoPlayVideo['users_id']);
            $autoPlayVideo['tags'] = Video::getTags($autoPlayVideo['id'], '<br /><small>' . humanTiming(strtotime($autoPlayVideo['videoCreation'])) . '</small>');
            $autoPlayVideo['url'] = Video::getLink($autoPlayVideo['id'], $autoPlayVideo['clean_title'], false, $get);
        }
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    }

    $modeYouTubeTimeLog['Code part 2'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    if (!empty($video) && !empty($video['users_id'])) {
        $name = User::getNameIdentificationById($video['users_id']);
        $name = "<a href='" . User::getChannelLink($video['users_id']) . "' class='btn btn-xs btn-default'>{$name} " . User::getEmailVerifiedIcon($video['users_id']) . "</a>";
        $subscribe = Subscribe::getButton($video['users_id']);
        $video['creator'] = Video::getCreatorHTML($video['users_id'], '<div class="clearfix"></div><small>' . humanTiming(strtotime(@$video['videoCreation'])) . '</small>');

        $obj = new Video("", "", $video['id']);
    }

    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    if (!empty($video) && $video['type'] == "video") {
        $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
    } else {
        $poster = "" . getURL("view/img/audio_wave.jpg");
    }

    if (!empty($video)) {
        $source = Video::getSourceFile($video['filename']);
        if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
            $img = $source['url'];
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } elseif ($video['type'] == "audio") {
            $img = "" . getURL("view/img/audio_wave.jpg");
        }
        $type = 'video';
        if ($video['type'] === 'pdf') {
            $type = 'pdf';
        } elseif ($video['type'] === 'zip') {
            $type = 'zip';
        } elseif ($video['type'] === 'article') {
            $type = 'article';
        }
        $images = Video::getImageFromFilename($video['filename'], $type);
        $poster = isMobile() ? $images->thumbsJpg : $images->poster;
        if (!empty($images->posterPortrait) && basename($images->posterPortrait) !== 'notfound_portrait.jpg' && basename($images->posterPortrait) !== 'pdf_portrait.png' && basename($images->posterPortrait) !== 'article_portrait.png') {
            $img = $images->posterPortrait;
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } else {
            $img = isMobile() ? $images->thumbsJpg : $images->poster;
        }
    } else {
        $poster = "" . getURL("view/img/notfound.jpg");
    }
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    $objSecure = AVideoPlugin::getObjectDataIfEnabled('SecureVideosDirectory');
    $modeYouTubeTimeLog['Code part 3'] = microtime(true) - $modeYouTubeTime;
    $modeYouTubeTime = microtime(true);
    if (!empty($autoPlayVideo) && !empty($autoPlayVideo['filename'])) {
        $autoPlaySources = getSources($autoPlayVideo['filename'], true);
        $autoPlayURL = $autoPlayVideo['url'];
        $autoPlayPoster = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}.jpg";
        $autoPlayThumbsSprit = "{$global['webSiteRootURL']}videos/{$autoPlayVideo['filename']}_thumbsSprit.jpg";
    } else {
        $autoPlaySources = [];
        $autoPlayURL = '';
        $autoPlayPoster = '';
        $autoPlayThumbsSprit = '';
    }
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);

    if (empty($_GET['videoName']) && !empty($video) && !empty($video['clean_title'])) {
        $_GET['videoName'] = $video['clean_title'];
    }
    if(!empty($video)){
        $v = Video::getVideo($video['id'], "", true, false, false, true);
    }else if (!empty($_GET['videoName'])) {
        $v = Video::getVideoFromCleanTitle($_GET['videoName']);
    }
    if (empty($v) && empty($videosPlayList[$playlist_index]['id'])) {
        if($_GET['playlist_id'] == 'favorite' || $_GET['playlist_id'] == 'watch-later'){
            if($_GET['playlist_id'] == 'favorite'){
                $msg = __('Your Favorite playlist is waiting to be filled! Start exploring and add the videos you love the most.');
            }else{
                $msg = __('Oops! Your Watch Later playlist is empty. Don\'t worry, we have plenty of exciting videos for you to choose from and add here.');
            }
            $url = addQueryStringParameter($global['webSiteRootURL'], 'msg', $msg);
            header("location: {$url}");
            exit;
        }else {
            $response = Video::whyUserCannotWatchVideo(User::getId(), @$video['id']);
            $html = "<ul><li>".implode('</li><li>', $response->why)."</li></ul>";
            videoNotFound($html);
        }
    } else {
        $modeYouTubeTimeLog['Code part 4'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
        AVideoPlugin::getModeYouTube($v['id']);
        $modeYouTubeTimeLog['Code part 5'] = microtime(true) - $modeYouTubeTime;
        $modeYouTubeTime = microtime(true);
    }
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
}


TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);

// video not found
if (empty($video)) {
    if (!empty($_GET['v'])) {
        $vid = new Video('', '', $_GET['v']);
        if ($vid->getStatus() === Video::$statusBrokenMissingFiles) {
            if (!Video::isMediaFileMissing($vid->getFilename())) {
                $vid->setStatus(Video::$statusActive);
                $vid->save();
                _error_log('Missing files recovered ' . $_GET['v']);
            } else {
                videoNotFound('ERROR 1: The video ID [' . $_GET['v'] . '] is not available: status=' . Video::$statusDesc[$vid->getStatus()]);
            }
        } else if ($vid->getStatus() === Video::$statusUnpublished) {
            videoNotFound('This video is currently unpublished. Please contact an administrator to review and approve it for publication. Thank you for your patience and understanding.');
        } else {
            videoNotFound('ERROR 2: The video ID [' . $_GET['v'] . '] is not available: status=' . Video::$statusDesc[$vid->getStatus()]);
        }
    } else {
        videoNotFound('ERROR 3: The video is not available video ID is empty');
    }
}

if (empty($video)) {
    videoNotFound('Please try again');
    exit;
}

if (!User::canWatchVideoWithAds($video['id'])) {
    forbiddenPage('This video is private');
    exit;
}


$metaDescription = " {$video['id']}";

// make sure the title tag does not have more then 70 chars
$titleTag = getSEOTitle($video['title']);
//$titleTag .= getSEOComplement(["allowedTypes" => ["audio", "video", "pdf"]]) . $config->getPageTitleSeparator() . $config->getWebSiteTitle();

if (!empty($video['users_id']) && User::hasBlockedUser($video['users_id'])) {
    $video['type'] = "blockedUser";
}

TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
global $nonCriticalCSS;
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>" prefix="og: http://ogp.me/ns#">
    <head>
        <title><?php echo $titleTag; ?></title>
        <link href="<?php echo getURL('node_modules/video.js/dist/video-js.min.css'); ?>" rel="stylesheet" type="text/css"  />
        <link href="<?php echo getCDN('plugin/Gallery/style.css'); ?>" rel="stylesheet" type="text/css"/>
        <?php
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        include $global['systemRootPath'] . 'view/include/head.php';

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (!empty($_GET['v'])) {
            getOpenGraph($_GET['v']);
            getLdJson($_GET['v']);
        } else {
            getOpenGraph(0);
            getLdJson(0);
        }
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        include $global['systemRootPath'] . 'view/include/navbar.php';

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        if (!empty($advancedCustomUser->showChannelBannerOnModeYoutube)) {
            ?>
            <div class="container" style="margin-bottom: 10px;">
                <img src="<?php echo User::getBackground($video['users_id']); ?>" class="img img-responsive" />
            </div>
            <?php
        }
        ?>
        <!-- view modeYoutube.php -->
        <div class="container-fluid principalContainer avideoLoadPage" id="modeYoutubePrincipal" style="overflow: hidden;">
            <?php
            if (!empty($video)) {
                if (empty($video['type'])) {
                    $video['type'] = "video";
                }
                
                TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
                require "{$global['systemRootPath']}view/modeYoutubeBundle.php";

                TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
            } else {
                ?>
                <br>
                <br>
                <br>
                <br>
                <div class="alert alert-warning">
                    <span class="glyphicon glyphicon-facetime-video"></span>
                    <strong><?php echo __("Attention"); ?>!</strong> <?php echo empty($advancedCustom->videoNotFoundText->value) ? __("We have not found any videos or audios to show") : $advancedCustom->videoNotFoundText->value; ?>.
                </div>
                <?php }
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/video.min.js.php';
        echo AVideoPlugin::afterVideoJS();
        include $global['systemRootPath'] . 'view/include/footer.php';
        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        ?>
        <script src="<?php echo getURL('view/js/BootstrapMenu.min.js'); ?>node_modules/videojs-playlist/dist/videojs-playlist.min.js"></script>
        <script>
            var fading = false;
        </script>

        <?php
        showCloseButton();

        TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
        ?>
    </body>
</html>
<?php
include $global['systemRootPath'] . 'objects/include_end.php';

TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
?>