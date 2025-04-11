<?php
//var_dump($_GET);exit;
global $global, $config, $isEmbed;
$modeYouTubeTime = microtime(true);
$isChannel = 1; // still workaround, for gallery-functions, please let it there.
$isModeYouTube = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
//var_dump(Video::getVideo(73, Video::SORT_TYPE_VIEWABLE, false, false, false, true));
//var_dump(Video::userGroupAndVideoGroupMatch(User::getId(), 73));exit;
//var_dump(Video::getUserGroups());
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

$videos_id = getVideos_id(!empty($_REQUEST['playlist_id']));
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

    $img = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
    $poster =  $img;
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
    _session_write_close();
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    if (empty($_GET['playlist_id']) && !empty($_GET['playlist_name'])) {
        $_GET['playlist_id'] = $_GET['playlist_name'];
    } else if (empty($_GET['playlist_id']) && !empty($_GET['playlists_id'])) {
        $_GET['playlist_id'] = $_GET['playlists_id'];
    }/*
    else{
        $video_ =  Video::getVideoLight($_GET['videos_id']);
        if(!empty($video_['serie_playlists_id'])){
            $_GET['playlist_id'] = $video_['serie_playlists_id'];
        }
    }*/
    if (!empty($_GET['playlist_id'])) {
        $isSerie = 1;
        $plp = new PlayListPlayer(@$_GET['playlist_id'], @$_GET['playlists_tags_id']);
        $playListData = $plp->getPlayListData();
        //var_dump($_GET['playlist_id'], $_GET['playlists_tags_id'], $playListData, $messagesFromPlayList);exit;
        if (!$plp->canSee()) {
            forbiddenPage(_('You cannot see this playlist') . ' ' . basename(__FILE__));
        }
        $playListData = $plp->getPlayListData();
        //var_dump($playListData);exit;
        if (empty($playListData)) {
            if (empty($messagesFromPlayList)) {
                $messagesFromPlayList = array();
            }
            _error_log(implode(PHP_EOL . "Playlist error: playlist_id={$_GET['playlist_id']}, playlists_tags_id={$_GET['playlists_tags_id']} - ", $messagesFromPlayList));

            $notFoundMessage = PlayLists::getPlaylistNotFoundMessage($_GET['playlist_id']);
            videoNotFound($notFoundMessage);
        }

        $video = $plp->getCurrentVideo();
        if (!empty($video)) {
            $videos_id = $_getVideos_id = intval($video['id']);
            $playlist_index = $plp->getIndex();
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

        if (empty($video) && !empty($videos_id)) {
            $video = Video::getVideo($videos_id, Video::SORT_TYPE_VIEWABLE, false, false, false, true);
            if (!empty($video)) {
                $attemptLog[] = "Video loaded successfully line=".__LINE__;
            }else{
                $attemptLog[] = "No video found using Video::getVideo with videos_id = $videos_id and show unlisted $lastGetVideoSQL ";
            }
            //var_dump($_GET, $video);exit;
            //var_dump('Line: '.__LINE__, $_REQUEST['v'], $video);exit;
        }
        $attemptLog = []; // Initialize the log tracker

        // Add log details as you attempt to load the video
        if (empty($video)) {
            $video = Video::getVideo("", Video::SORT_TYPE_VIEWABLE, false, false, true, true);
            if (!empty($video)) {
                $attemptLog[] = "Video loaded successfully line=".__LINE__;
            }else{
                $attemptLog[] = "No video found using Video::getVideo Suggested only and show unlisted $lastGetVideoSQL ";
            }
        }

        if (empty($video)) {
            $video = Video::getVideo("", Video::SORT_TYPE_VIEWABLE, false, false, false, true);
            if (!empty($video)) {
                $attemptLog[] = "Video loaded successfully line=".__LINE__;
            }else{
                $attemptLog[] = "Fallback attempt using Video::getVideo search all videos and show unlisted  $lastGetVideoSQL";
            }
        }

        if (empty($video)) {
            $video = AVideoPlugin::getVideo();
            if (!empty($video)) {
                $attemptLog[] = "Video loaded successfully line=".__LINE__;
            }else{
                $attemptLog[] = "Not found attempt with AVideoPlugin::getVideo failed.  $lastGetVideoSQL";
            }
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
            if (!empty($_GET['debug'])) {
                //var_dump(__FILE__, __LINE__);
            }
        } else {
            $modeYouTubeTimeLog['Code part 1.3'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            $modeYouTubeTimeLog['Code part 1.5'] = microtime(true) - $modeYouTubeTime;
            $modeYouTubeTime = microtime(true);
            if (!empty($video['id'])) {
                //$numRows = Video::getTotalVideos(Video::SORT_TYPE_SUGGESTED, false, false, false, true, true);
                /*
                $autoPlayVideo = Video::getRandom($video['id'], Video::SORT_TYPE_SUGGESTED);
                if(!empty($_GET['debug'])){
                    var_dump(__FILE__, __LINE__, $lastGetVideoSQL, $lastGetTotalVideos);
                }
                */
                if (empty($autoPlayVideo) || empty($autoPlayVideo['id'])) {
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
        $html = '';
        if (!empty($advancedCustom->showCreationTimeOnVideoItem)) {
            $created = !empty($video['videoCreation']) ? $video['videoCreation'] : $video['created'];
            $html = '<div class="clearfix"></div><small>' . humanTiming(_strtotime($created)) . '</small>';
        } else {
            $html = '<!-- empty showCreationTimeOnVideoItem ' . basename(__FILE__) . ' line=' . __LINE__ . '-->';
        }
        $video['creator'] = Video::getCreatorHTML($video['users_id'], $html);

        $obj = new Video("", "", $video['id']);
    }

    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
    if (!empty($video) && $video['type'] == Video::$videoTypeVideo) {
        $poster = "{$global['webSiteRootURL']}videos/{$video['filename']}.jpg";
    } else {
        $poster = ImagesPlaceHolders::getAudioLandscape(ImagesPlaceHolders::$RETURN_URL);
    }

    if (!empty($video)) {
        $source = Video::getSourceFile($video['filename']);
        if (($video['type'] !== "audio") && ($video['type'] !== "linkAudio") && !empty($source['url'])) {
            $img = $source['url'];
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } elseif ($video['type'] == "audio") {
            $img = ImagesPlaceHolders::getAudioLandscape(ImagesPlaceHolders::$RETURN_URL);
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
        if (!empty($images->posterPortrait) && !ImagesPlaceHolders::isDefaultImage($images->posterPortrait)) {
            $img = $images->posterPortrait;
            $data = getimgsize($source['path']);
            $imgw = $data[0];
            $imgh = $data[1];
        } else {
            $img = isMobile() ? $images->thumbsJpg : $images->poster;
        }
    } else {
        $poster = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL);
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
    if (!empty($video)) {
        $v = Video::getVideo($video['id'], "", true, false, false, true);
    } else if (!empty($_GET['videoName'])) {
        $v = Video::getVideoFromCleanTitle($_GET['videoName']);
    }
    // Final check before forbiddenPage
    if (empty($v) && empty($videosPlayList[$playlist_index]['id'])) {
        if ($_GET['playlist_id'] == 'favorite' || $_GET['playlist_id'] == 'watch-later') {
            $attemptLog[] = "Playlist ID '{$playlist_id}' is either 'favorite' or 'watch-later' but no videos were found.";
        } else if (!empty($video['id'])) {
            $response = Video::whyUserCannotWatchVideo(User::getId(), @$video['id']);
            $html = "<ul><li>" . implode('</li><li>', $response->why) . "</li></ul>";
            $attemptLog[] = "User is not allowed to watch video ID: {$video['id']}. Reasons: " . implode(', ', $response->why);
            videoNotFound($html);
        } else {
            $attemptLog[] = "Final fallback attempt in AVideoPlugin::getModeYouTube failed for video ID: {$videos_id}";
            AVideoPlugin::getModeYouTube($videos_id);
        }

        // Log the actions before calling forbiddenPage
        foreach ($attemptLog as $key => $value) {
            _error_log("Video load attempts: {$value}");
        }
        forbiddenPage(__('We could not load the video') . " Video ID = {$videos_id}");
    }
    TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
}

//_error_log('AVideoPlugin::getModeYouTube');
AVideoPlugin::getModeYouTube($videos_id);
//var_dump(__LINE__);exit;
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
                if (!User::isLogged()) {
                    gotToLoginAndComeBackHere();
                } else {
                    $msg = 'ERROR 1: The video ID [' . $vid->getId() . '] is not available: status=' . Video::$statusDesc[$vid->getStatus()];
                    _error_log($msg. ' filename=' . $vid->getFilename());
                    videoNotFound($msg);
                }
                exit;
            }
        } else if ($vid->getStatus() === Video::$statusUnpublished) {
            $msg = 'This video is currently unpublished. Please contact an administrator to review and approve it for publication. Thank you for your patience and understanding.';
            videoNotFound($msg);
            exit;
        } else {
            if (!User::isLogged()) {
                gotToLoginAndComeBackHere();
            } else {
                $status = $vid->getStatus();

                switch ($status) {
                    case 'i':
                        $msg = "The video ID [{$_GET['v']}] is currently inactive. Please check back later or contact support.";
                        break;
                    case 'b':
                        $msg = "There seems to be an issue with your video ID [{$_GET['v']}]: Broken or missing files. Please contact support for assistance.";
                        break;
                    case 'p':
                        $msg = "The video ID [{$_GET['v']}] is unpublished. If this is unexpected, please reach out to support for more information.";
                        break;
                    default:
                        $msg = "The video ID [{$_GET['v']}] status {$status}. Please contact support for further assistance.";
                        break;
                }
                videoNotFound($msg);
            }
            exit;
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

// load the funcitons for chapters
AVideoPlugin::loadPluginIfEnabled('Bookmark');

$metaDescription = " {$video['id']}";

// make sure the title tag does not have more then 70 chars
$titleTag = getSEOTitle($video['title']);
//$titleTag .= getSEOComplement(["allowedTypes" => ["audio", "video", "pdf"]]) . $config->getPageTitleSeparator() . $config->getWebSiteTitle();

if (!empty($video['users_id']) && User::hasBlockedUser($video['users_id'])) {
    $video['type'] = "blockedUser";
}

TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
global $nonCriticalCSS;

$_page = new Page(array($titleTag));
$_page->setExtraStyles(
    array(
        'node_modules/video.js/dist/video-js.min.css',
        'plugin/Gallery/style.css'
    )
);
?>
<?php
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
            <i class="fa-solid fa-video"></i>
            <strong><?php echo __("Attention"); ?>!</strong> <?php echo empty($advancedCustom->videoNotFoundText->value) ? __("We have not found any videos or audios to show") : $advancedCustom->videoNotFoundText->value; ?>.
        </div>
    <?php
    }
    ?>
</div>
<?php
include $global['systemRootPath'] . 'view/include/video.min.js.php';
echo AVideoPlugin::afterVideoJS();
TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
?>
<script>
    var fading = false;
</script>
<?php
showCloseButton();

TimeLogEnd($timeLogNameMY, __LINE__, $TimeLogLimitMY);
?>
<?php
$_page->print();
?>
