<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

class PlayerSkins extends PluginAbstract
{

    static public $hasMarks = false;

    public function getTags()
    {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription()
    {
        global $global;
        $desc = "Customize your playes Skin<br>The Skis options are: ";
        $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
        $names = array();
        foreach (glob($dir . '*.css') as $file) {
            $path_parts = pathinfo($file);
            $names[] = $path_parts['filename'];
        }
        $desc .= $desc . "<code>" . implode("</code> or <code>", $names) . "</code>";

        //$dir = $global['systemRootPath'] . 'plugin/PlayerSkins/epg.php';
        //$desc .= "<br>crontab for auto generate cache for EPG links <code>0 * * * * php {$dir}</code>";

        return $desc;
    }

    public function getName()
    {
        return "PlayerSkins";
    }

    public function getUUID()
    {
        return "e9a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion()
    {
        return "1.1";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->skin = "avideo";
        $obj->playbackRates = "[0.25, 0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]";
        $obj->playerCustomDataSetup = "";
        $obj->showSocialShareOnEmbed = true;
        $obj->showLoopButton = true;
        $obj->showPictureInPicture = true;

        $o = new stdClass();
        $o->type = array(0 => 'Show In all devices', 1 => 'Show In Mobile Only', 2 => 'Show In Desktop Only');
        $o->value = 0;
        $obj->showFullscreenToggle = $o;

        $obj->showLogo = false;
        $obj->showShareSocial = true;
        $obj->showShareAutoplay = true;
        $obj->forceAlwaysAutoplay = false;
        $obj->showLogoOnEmbed = false;
        $obj->showLogoAdjustScale = "0.4";
        $obj->showLogoAdjustLeft = "-74px";
        $obj->showLogoAdjustTop = "-22px;";
        $obj->disableEmbedTopInfo = false;
        $obj->contextMenuDisableEmbedOnly = false;
        $obj->contextMenuLoop = true;
        $obj->contextMenuCopyVideoURL = true;
        $obj->contextMenuCopyVideoURLCurrentTime = true;
        $obj->contextMenuCopyEmbedCode = true;
        $obj->contextMenuShare = true;
        $obj->playerFullHeight = false;
        $obj->playsinline = true;
        $obj->showVideoSEOViewForBots = true;

        $obj->hideButtonFromPlayerIfIsSmallPictureInPicture = true;
        $obj->hideButtonFromPlayerIfIsSmallPlayerLogo = true;
        $obj->hideButtonFromPlayerIfIsSmallSeek = true;
        $obj->hideButtonFromPlayerIfIsSmallLoop = true;
        $obj->hideButtonFromPlayerIfIsSmallAutoplay = true;

        $obj->autoGenerateAndCacheEPG = false;

        $obj->chromeCast = false;
        $obj->airPlay = false;

        return $obj;
    }

    static function getPlaysinline()
    {
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        if ($obj->playsinline) {
            return ' playsinline webkit-playsinline="webkit-playsinline" ';
        }
        return '';
    }

    static function isYoutubeIntegrationEnabled()
    {
        global $advancedCustom;
        if (isMobile()) {
            return false;
        }

        if (empty($advancedCustom)) {
            $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
        }
        return empty($advancedCustom->disableYoutubePlayerIntegration);
    }

    static function getMediaTag($filename, $htmlMediaTag = false)
    {
        global $autoPlayURL, $global, $config, $isVideoTypeEmbed, $advancedCustom;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        $html = '';
        if (empty($htmlMediaTag)) {
            $video = Video::getVideoFromFileName($filename, true);
            $vType = Video::getIncludeType($video);
            $_GET['isMediaPlaySite'] = $video['id'];
            if (is_object($video['externalOptions'])) {
                if (!empty($video['externalOptions']->videoStartSeconds)) {
                    $video['externalOptions']->videoStartSeconds = parseDurationToSeconds($video['externalOptions']->videoStartSeconds);
                } else {
                    $video['externalOptions']->videoStartSeconds = 0;
                }
            } else {
                //_error_log('externalOptions Error '.$video['externalOptions'], AVideoLog::$WARNING);
                $video['externalOptions'] = new stdClass();
                $video['externalOptions']->videoStartSeconds = 0;
            }
            $images = Video::getImageFromFilename($filename);
            if ($vType == 'video') {
                $htmlMediaTag = '<video ' . self::getPlaysinline()
                    . 'preload="auto" poster="' . $images->poster . '" controls controlsList="nodownload"
                        class="embed-responsive-item video-js vjs-default-skin vjs-big-play-centered vjs-16-9" id="mainVideo">';
                if ($video['type'] == Video::$videoTypeVideo) {
                    $sources = getSources($video['filename']);
                    //var_dump($video['filename'], $sources);exit;
                    $htmlMediaTag .= PHP_EOL . "<!-- Video title={$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= PHP_EOL . $sources; //var_dump($sources);exit;
                } else { // video link
                    $url = AVideoPlugin::modifyURL($video['videoLink'], $video['id']);
                    //var_dump($video['videoLink'], $url);exit;
                    $htmlMediaTag .= PHP_EOL . "<!-- Video Link {$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= PHP_EOL . "<source src='{$url}' type='" . mime_content_type_per_filename($video['videoLink']) . "' >";
                    $html .= "<script>$(document).ready(function () {\$('time.duration').hide();});</script>";
                }
                $htmlMediaTag .= '<p>' . __("If you can't view this video, your browser does not support HTML5 videos") . '</p><p class="vjs-no-js">' . __("To view this video please enable JavaScript, and consider upgrading to a web browser that") . '<a href="http://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer">supports HTML5 video</a></p></video>';
            } else if ($vType == 'audio') {
                $htmlMediaTag = '<audio ' . self::getPlaysinline() . '
                       preload="auto"
                       poster="' . $images->poster . '" controls class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" id="mainVideo">';
                if ($video['type'] == "audio" || Video::forceAudio()) {
                    $htmlMediaTag .= "<!-- Audio {$video['title']} {$video['filename']} -->" . getSources($video['filename']);
                } else { // audio link
                    if (file_exists($global['systemRootPath'] . "videos/" . $video['filename'] . ".ogg")) {
                        $type = "audio/ogg";
                    } else {
                        $type = "audio/mpeg";
                    }
                    $htmlMediaTag .= "<!-- Audio Link {$video['title']} {$video['filename']} --><source src='{$video['audioLink']}' type='" . $type . "' >";
                    $html .= "<script>$(document).ready(function () {\$('time.duration').hide();});</script>";
                }
                $htmlMediaTag .= '</audio>';
            } else if ($vType == 'embed') {
                $disableYoutubeIntegration = !PlayerSkins::isYoutubeIntegrationEnabled();
                $_GET['isEmbedded'] = "";
                if (
                    ($disableYoutubeIntegration) ||
                    (
                        (strpos($video['videoLink'], "youtu.be") == false) && (strpos($video['videoLink'], "youtube.com") == false)
                        //&& (strpos($video['videoLink'], "vimeo.com") == false)
                    )
                ) {
                    $_GET['isEmbedded'] = "e";
                    $isVideoTypeEmbed = 1;
                    $url = parseVideos($video['videoLink']);
                    if ($config->getAutoplay()) {
                        $url = addQueryStringParameter($url, 'autoplay', 1);
                    }
                    $htmlMediaTag = "<!-- Embed Link 1 {$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= '<video ' . self::getPlaysinline() . ' id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
                    //$htmlMediaTag .= '<div id="main-video" class="embed-responsive-item">';
                    $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" ' . Video::$iframeAllowAttributes . ' src="' . $url . '"></iframe>';
                    //$htmlMediaTag .= '</div>';
                } else {
                    // youtube!
                    if ((stripos($video['videoLink'], "youtube.com") != false) || (stripos($video['videoLink'], "youtu.be") != false)) {
                        $_GET['isEmbedded'] = "y";
                    } else if ((stripos($video['videoLink'], "vimeo.com") != false)) {
                        $_GET['isEmbedded'] = "v";
                    }
                    $_GET['isMediaPlaySite'] = $video['id'];
                    PlayerSkins::playerJSCodeOnLoad($video['id'], @$video['url']);
                    $htmlMediaTag = "<!-- Embed Link 2 YoutubeIntegration {$video['title']} {$video['filename']} -->";
                    $htmlMediaTag .= '<video ' . self::getPlaysinline() . ' id="mainVideo" class="embed-responsive-item video-js vjs-default-skin vjs-16-9 vjs-big-play-centered" controls controlsList="nodownload"></video>';
                    $htmlMediaTag .= '<script>var player;mediaId = ' . $video['id'] . ';$(document).ready(function () {$(".vjs-control-bar").css("opacity: 1; visibility: visible;");});</script>';
                }
            } else if ($vType == 'serie') {
                $isVideoTypeEmbed = 1;
                $link = "{$global['webSiteRootURL']}plugin/PlayLists/embed.php";
                $link = addQueryStringParameter($link, 'playlists_id', $video['serie_playlists_id']);
                $link = addQueryStringParameter($link, 'autoplay', $config->getAutoplay());
                $link = addQueryStringParameter($link, 'playlist_index', @$_REQUEST['playlist_index']);

                $htmlMediaTag = "<!-- Serie {$video['title']} {$video['filename']} -->";
                $htmlMediaTag .= '<video ' . self::getPlaysinline() . ' id="mainVideo" style="display: none; height: 0;width: 0;" ></video>';
                $htmlMediaTag .= '<iframe class="embed-responsive-item" scrolling="no" ' . Video::$iframeAllowAttributes . ' src="' . $link . '"></iframe>';
                $htmlMediaTag .= '<script>$(document).ready(function () {addView(' . intval($video['id']) . ', 0);});</script>';
            }

            $html .= "<script>mediaId = '{$video['id']}';var player;" . self::playerJSCodeOnLoad($video['id'], @$autoPlayURL) . '</script>';
        }

        /*
          $col1Classes = 'col-md-2 firstC';
          $col2Classes = 'col-md-8 secC';
          $col3Classes = 'col-md-2 thirdC';
          if ($obj->playerFullHeight) {
          $col2Classes .= ' text-center playerFullHeight';
          }

          $html .= '
          <div class="row main-video" id="mvideo">
          <div class="' . $col1Classes . '"></div>
          <div class="' . $col2Classes . '">
          <div id="videoContainer">
          <div id="floatButtons" style="display: none;">
          <p class="btn btn-outline btn-xs move">
          <i class="fas fa-expand-arrows-alt"></i>
          </p>
          <button type="button" class="btn btn-outline btn-xs"
          onclick="closeFloatVideo(); floatClosed = 1;">
          <i class="fas fa-times"></i>
          </button>
          </div>
          <div id="main-video" class="embed-responsive embed-responsive-16by9">' . $htmlMediaTag . '</div>';
         */

        //$html .= showCloseButton() . '</div></div><div class="' . $col3Classes . '"></div></div>';
        $html .= getMVideo($htmlMediaTag);

        return $html;
    }

    public function getHeadCode()
    {
        global $global, $config, $video;
        if (!empty($global['isForbidden'])) {
            return '';
        }
        if (is_object($video)) {
            $video = Video::getVideoLight($video->getId());
        }
        $obj = $this->getDataObject();
        $css = "";
        $js = "";
        $js .= "<script>var _adWasPlayed = 0;</script>";
        if (isLive()) {
            $js .= "<script>var isLive = true;</script>";
        }
        if (isVideo() || !empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {

            if (self::showSkipIntro()) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/skipIntro.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }

            if (!empty($_REQUEST['autoplay']) || !empty($obj->forceAlwaysAutoplay)) {
                $js .= "<script>var autoplay = true;var forceautoplay = true;</script>";
            } else if (self::isAutoplayEnabled()) {
                $js .= "<script>var autoplay = true;</script>";
            } else {
                $js .= "<script>var autoplay = false;</script>";
            }
            $js .= "<script>var playNextURL = '';</script>";
            if (!empty($obj->skin)) {
                $url = "plugin/PlayerSkins/skins/{$obj->skin}.css";
                $css .= "<link href=\"" . getURL($url) . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if ($obj->showLoopButton && isVideoPlayerHasProgressBar()) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/loopbutton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            $css .= "<link href=\"" . getURL('plugin/PlayerSkins/player.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";

            $classes = [];

            if ($obj->hideButtonFromPlayerIfIsSmallPictureInPicture) {
                $classes[] = '.vjs-picture-in-picture-control';
            }
            if ($obj->hideButtonFromPlayerIfIsSmallPlayerLogo) {
                $classes[] = '.player-logo';
            }
            if ($obj->hideButtonFromPlayerIfIsSmallSeek) {
                $classes[] = '.vjs-seek-button';
            }
            if ($obj->hideButtonFromPlayerIfIsSmallLoop) {
                $classes[] = '.loop-button';
            }
            if ($obj->hideButtonFromPlayerIfIsSmallAutoplay) {
                $classes[] = '.autoplay-button';
            }

            if (!empty($classes)) {
                $css .= "<style> @media (max-width: 768px) {";
                $css .= implode(', ', $classes);
                $css .= "{display: none !important;}";
                $css .= "} </style>";
            }
            if (self::includeFullscreenBlock()) {
                $css .= "<style>";
                $css .= ".video-js .vjs-fullscreen-control {display: none;}";
                $css .= "</style>";
            }

            $css .= "<script src=\"" . getURL('plugin/PlayerSkins/player.js') . "\"></script>";
            if ($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo) {
                $logo = "{$global['webSiteRootURL']}" . $config->getLogo(true);
                $css .= "<style>"
                    . ".player-logo{
                            outline: none;
                            filter: grayscale(100%);
                            width:100px !important;
                            }
                            .player-logo:hover{
                            filter: none;
                            -webkit-filter: drop-shadow(1px 1px 1px rgba(255, 255, 255, 0.5));
                            filter: drop-shadow(1px 1px 1px rgba(255, 255, 255, 0.5));
                            }
                            .player-logo:before {
                                display: inline-block;
                                content: url({$logo});
                                transform: scale({$obj->showLogoAdjustScale});
                            position: relative;
                            left:{$obj->showLogoAdjustLeft};
                            top:{$obj->showLogoAdjustTop};

                            }
                        </style>";
            }

            if ($obj->showShareSocial && CustomizeUser::canShareVideosFromVideo(@$video['id'])) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/shareButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if ($obj->showShareAutoplay && isVideoPlayerHasProgressBar() && empty($obj->forceAlwaysAutoplay) && empty($_REQUEST['hideAutoplaySwitch'])) {
                $css .= "<link href=\"" . getURL('plugin/PlayerSkins/autoplayButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
        }
        $videos_id = getVideos_id();
        if (!empty($videos_id) && Video::getEPG($videos_id)) {
            $css .= "<link href=\"" . getURL('plugin/PlayerSkins/epgButton.css') . "\" rel=\"stylesheet\" type=\"text/css\"/>";
        }

        $url = urlencode(getSelfURI());
        $oembed = '<link href="' . getCDN() . 'oembed/?format=json&url=' . $url . '" rel="alternate" type="application/json+oembed" />';
        $oembed .= '<link href="' . getCDN() . 'oembed/?format=xml&url=' . $url . '" rel="alternate" type="application/xml+oembed" />';


        $plugins = new stdClass();
        $onPlayerReady = '';
        $getDataSetup = ', controls: true';
        $addStartPlayerJS = false;

        if ($obj->chromeCast) {
            if (isVideoOrAudioNotEmbed()) {
                $css .= '<link href="' . getURL('node_modules/@silvermine/videojs-chromecast/dist/silvermine-videojs-chromecast.css') . '" rel="stylesheet" type="text/css"/>';
                $css .= "<style>.vjs-chromecast-button .vjs-icon-placeholder {width: 20px;height: 20px;}</style>";
                $js .= '<script>window.SILVERMINE_VIDEOJS_CHROMECAST_CONFIG = {preloadWebComponents: true};</script>';
                $js .= '<script src="' . getURL('node_modules/@silvermine/videojs-chromecast/dist/silvermine-videojs-chromecast.min.js') . '"></script>';
                $js .= '<script src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1" type="text/javascript"></script>';
                $onPlayerReady .= 'player.chromecast();player.on(\'play\', function () {player.chromecast();});';
                $getDataSetup .= ", techOrder: ['chromecast', 'html5'] ";
                $plugins->chromecast = new stdClass();
                $addStartPlayerJS = true;
            }
        }

        if ($obj->airPlay) {
            if (isVideoOrAudioNotEmbed()) {
                $css .= '<link href="' . getURL('node_modules/@silvermine/videojs-airplay/dist/silvermine-videojs-airplay.css') . '" rel="stylesheet" type="text/css"/>';
                $js .= '<script src="' . getURL('node_modules/@silvermine/videojs-airplay/dist/silvermine-videojs-airplay.min.js') . '"></script>';
                $plugins->airPlay = new stdClass();
                $plugins->airPlay->addButtonToControlBar = true;
                $addStartPlayerJS = true;
            }
        }
        $js .= '<script src="' . getURL('plugin/PlayerSkins/events/playerAdsFunctions.js') . '"></script>';

        if ($addStartPlayerJS) {
            //var_dump($onPlayerReady, $getDataSetup . ", plugins: " . json_encode($plugins));exit;
            echo '<script>' . PlayerSkins::getStartPlayerJS($onPlayerReady, $getDataSetup . ", plugins: " . json_encode($plugins)) . '</script>';
        }

        return $js . $css . $oembed;
    }

    static function showAutoplay()
    {
        $obj = AVideoPlugin::getDataObject('PlayerSkins');
        return !isLive() && $obj->showShareAutoplay && isVideoPlayerHasProgressBar() && empty($obj->forceAlwaysAutoplay) && empty($_REQUEST['hideAutoplaySwitch']);
    }

    public function getStart()
    {
        global $global;
        /*
        if (!isBot()) {
            $obj = AVideoPlugin::getObjectData('PlayerSkins');
            if ($obj->showVideoSEOViewForBots) {
                include "{$global['systemRootPath']}plugin/PlayerSkins/seo.php";
            }
        }
         *
         */
    }

    static function showSkipIntro()
    {
        $videos_id = getVideos_id();
        $video = Video::getVideoLight($videos_id);
        $video['externalOptions'] = _json_decode($video['externalOptions']);

        if (!empty($video['externalOptions']->videoSkipIntroSecond)) {
            return parseDurationToSeconds($video['externalOptions']->videoSkipIntroSecond);
        }
        return 0;
    }

    public function getFooterCode()
    {
        global $global, $config, $getStartPlayerJSWasRequested, $video, $url, $title;
        $js = "<!-- playerSkin -->";
        $obj = $this->getDataObject();
        if (!empty($obj->forceAlwaysAutoplay)) {
            $js .= "<script>$(document).ready(function () {enableAutoPlay();});</script>";
        }
        if (
            !empty($_GET['videoName']) ||
            !empty($_GET['u']) ||
            !empty($_GET['evideo']) ||
            !empty($_GET['playlists_id']) ||
            (is_array($video) && !empty($video['id']))
        ) {
            if (empty($obj->showLoopButton) && empty($obj->contextMenuLoop)) {
                $js .= "<script>setPlayerLoop(false);</script>";
            }
            if ($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo) {
                $title = $config->getWebSiteTitle();
                //$url = "{$global['webSiteRootURL']}{$config->getLogo(true)}";
                $js .= "<script>var PlayerSkinLogoTitle = '{$title}';</script>";
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/logo.js"));
                //$js .= "<script src=\"".getCDN()."plugin/PlayerSkins/logo.js\"></script>";
            }
            if ($obj->showPictureInPicture) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/pipButton.js"));
            }
            if (self::includeFullscreenBlock()) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/fullscrenCheck.js"));
            }
            if ($obj->showShareSocial && CustomizeUser::canShareVideosFromVideo(@$video['id'])) {
                $social = getSocialModal(@$video['id'], @$url, @$title);
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/shareButton.js"));
                $js .= $social['html'];
                $js .= "<script>function tooglePlayersocial(){showSharing{$social['id']}();}</script>";
            }

            if ($skipTime = self::showSkipIntro()) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/skipintro.js"));
                $js .= "<script>var skipintroTime = {$skipTime};</script>";
            }

            if (self::showAutoplay()) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/autoplayButton.js"));
            } else {
                if (isLive()) {
                    $js .= "<!-- PlayerSkins is live, do not show autoplay -->";
                }
                if ($obj->showShareAutoplay) {
                    $js .= "<!-- PlayerSkins showShareAutoplay -->";
                }
                if (isVideoPlayerHasProgressBar()) {
                    $js .= "<!-- PlayerSkins isVideoPlayerHasProgressBar -->";
                }
                if (empty($obj->forceAlwaysAutoplay)) {
                    $js .= "<!-- PlayerSkins empty(\$obj->forceAlwaysAutoplay) -->";
                }
                if (empty($_REQUEST['hideAutoplaySwitch'])) {
                    $js .= "<!-- PlayerSkins empty(\$_REQUEST['hideAutoplaySwitch']) -->";
                }
            }
            $videos_id = getVideos_id();

            $event = "if(typeof updateMediaSessionMetadata === \"function\"){updateMediaSessionMetadata();}";
            PlayerSkins::getStartPlayerJS($event);
            if (!empty($videos_id) && Video::getEPG($videos_id)) {
                PlayerSkins::getStartPlayerJS(file_get_contents("{$global['systemRootPath']}plugin/PlayerSkins/epgButton.js"));
            }
        }
        if (isAudio()) {
            $videos_id = getVideos_id();
            $video = Video::getVideoLight($videos_id);
            $spectrumSource = Video::getSourceFile($video['filename'], "_spectrum.jpg");
            if (empty($spectrumSource["path"])) {
                if (AVideoPlugin::isEnabledByName('MP4ThumbsAndGif') && method_exists('MP4ThumbsAndGif', 'getSpectrum')) {
                    if (MP4ThumbsAndGif::getSpectrum($videos_id)) {
                        $spectrumSource = Video::getSourceFile($video['filename'], "_spectrum.jpg");
                    }
                }
            }
            if (!empty($spectrumSource["path"])) {
                $onPlayerReady = "startAudioSpectrumProgress('{$spectrumSource["url"]}');";
                self::prepareStartPlayerJS($onPlayerReady);
            }
        }
        if (empty($global['doNotLoadPlayer']) && !empty($getStartPlayerJSWasRequested) || isVideo()) {
            $js .= "<script src=\"" . getURL('view/js/videojs-persistvolume/videojs.persistvolume.js') . "\"></script>";
            $js .= "<script>" . self::getStartPlayerJSCode() . "</script>";
        }

        include $global['systemRootPath'] . 'plugin/PlayerSkins/mediaSession.php';
        PlayerSkins::addOnPlayerReady('if(typeof updateMediaSessionMetadata === "function"){updateMediaSessionMetadata();}');

        if (self::$hasMarks) {
            $js .= '<link href="' . getURL('plugin/AD_Server/videojs-markers/videojs.markers.css') . '" rel="stylesheet" type="text/css"/>';
            $js .= '<script src="' . getURL('plugin/AD_Server/videojs-markers/videojs-markers.js') . '"></script>';
        }

        return $js;
    }

    static function includeFullscreenBlock()
    {
        //$o->type = array(0=>'Show In all devices', 1=>'Show In Mobile Only', 2=>'Show In Desktop Only');
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        //var_dump($obj->showFullscreenToggle->value);exit;
        if (!empty($obj->showFullscreenToggle->value)) {
            if (($obj->showFullscreenToggle->value == 1 && !isMobile()) || $obj->showFullscreenToggle->value == 2) {
                return true;
            }
        }
        return false;
    }

    static function getDataSetup($str = "")
    {
        global $video, $disableYoutubeIntegration, $global;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');

        $dataSetup = array();

        //$dataSetup[] = "inactivityTimeout: 0";
        $dataSetup[] = "errorDisplay: false";
        if (isVideoPlayerHasProgressBar() && !empty($obj->playbackRates)) {
            $dataSetup[] = "'playbackRates':{$obj->playbackRates}";
        }
        if (isVideoPlayerHasProgressBar() && (isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false) && !empty($video['videoLink'])) {
            if ($_GET['isEmbedded'] == "y") {
                $dataSetup[] = "techOrder:[\"youtube\"]";
                $dataSetup[] = "sources:[{type: \"video/youtube\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "youtube:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            } else if ($_GET['isEmbedded'] == "v") {
                $dataSetup[] = "techOrder:[\"vimeo\"]";
                $dataSetup[] = "sources:[{type: \"video/vimeo\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "vimeo:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            }
        }
        $controlBar = array();
        if (!$obj->showPictureInPicture) {
            $controlBar[] = 'pictureInPictureToggle: false';
        }
        if (self::includeFullscreenBlock()) {
            //$controlBar[] = "fullscreenToggle: false";
        }
        if (!empty($controlBar)) {
            $dataSetup[] = "controlBar: {" . implode(', ', $controlBar) . "}";
        }

        $pluginsDataSetup = AVideoPlugin::dataSetup();
        if (!empty($pluginsDataSetup)) {
            $dataSetup[] = $pluginsDataSetup;
        }
        if (!empty($dataSetup)) {
            return ",{" . implode(",", $dataSetup) . "{$str}{$obj->playerCustomDataSetup}}";
        }

        return "";
    }

    // this function was modified, maybe removed in the future
    static function getStartPlayerJS($onPlayerReady = "", $getDataSetup = "", $noReadyFunction = false)
    {
        global $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup;
        global $getStartPlayerJSWasRequested;
        self::prepareStartPlayerJS($onPlayerReady, $getDataSetup);
        //var_dump('getStartPlayerJSWasRequested', debug_backtrace());
        $getStartPlayerJSWasRequested = true;
        //return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '", $onPlayerReady = "' . $onPlayerReady . '", $getDataSetup = "' . $getDataSetup . '" */';
        return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '" */';
    }

    static function addOnPlayerReady($onPlayerReady)
    {
        return self::getStartPlayerJS($onPlayerReady);
    }

    static function getStartPlayerJSCode($noReadyFunction = false, $currentTime = 0)
    {
        global $config, $global, $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup, $IMAADTag;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        $js = "";
        if (empty($currentTime) && isVideoPlayerHasProgressBar()) {
            $currentTime = self::getCurrentTime();
        }

        if (!empty($global['doNotLoadPlayer'])) {
            return '';
        }

        if (empty($prepareStartPlayerJS_onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady = array();
        }
        if (empty($prepareStartPlayerJS_getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup = array();
        }
        if (empty($noReadyFunction)) {
            $js .= "var originalVideo;";
            $js .= "var currentTime = $currentTime;";
            $js .= "var adTagOptions = {};";
            $js .= "var _adTagUrl = '{$IMAADTag}'; var player; ";
            $js .= "var startEvent = 'click';";
        }
        $js .= PHP_EOL . " $(document).ready(function () { " . PHP_EOL;
        $js .= "originalVideo = $('#mainVideo').clone();
        if (typeof player === 'undefined' && $('#mainVideo').length) {
            player = videojs('mainVideo'" . (self::getDataSetup(implode(" ", $prepareStartPlayerJS_getDataSetup))) . ");";
        //var_dump($IMAADTag, isVideoPlayerHasProgressBar());exit;
        if (!empty($IMAADTag) && isVideoPlayerHasProgressBar()) {
            $autoPlayAdBreaks = true; // this is to make it work on livestreams
            $adTagOptions = array(
                'id' => 'mainVideo',
                'adTagUrl' => $IMAADTag,
                'debug' => true,
                // 'useStyledLinearAds' => false,
                // 'useStyledNonLinearAds' => true,
                'forceNonLinearFullSlot' => true,
                'adLabel' => __('Advertisement'),
                'autoPlayAdBreaks' => $autoPlayAdBreaks,
            );
            $js .= PHP_EOL . "adTagOptions = " . json_encode($adTagOptions) . ";" . PHP_EOL;
            $js .= "player.ima(adTagOptions);";
            if(empty($autoPlayAdBreaks)){
                $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/vmap_ad_scheduler.js') . PHP_EOL;
            }
            if (isMobile()) {
                $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerAdsEventsMobile.js') . PHP_EOL;
            }
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerAdsEvents.js') . PHP_EOL;
        }
        $js .= "};" . PHP_EOL;

        $js .= PHP_EOL . "if(typeof player !== 'undefined'){";
        $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerReady.js');

        // this is here because for some reason videos on the storage only works if it loads dinamically on android devices only
        if (isMobile()) {
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerReadyMobile.js');
        }
        if (empty($_REQUEST['mute'])) {
            if (empty($global['ignorePersistVolume'])) {
                $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerReadyUnmuted.js');
            }
        } else {
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/events/playerReadyMuted.js');
        }

        $js .= "player.ready(function () {";
        $js .= "    try {";
        $js .= implode(' } catch (e) {console.error(\'onPlayerReady\', e);};try { ', $prepareStartPlayerJS_onPlayerReady) . ";";
        $js .= "    } catch (e) {";
        $js .= "        console.error('onPlayerReady', e);";
        $js .= "    }";
        $js .= "});";

        if ($obj->showLoopButton && isVideoPlayerHasProgressBar()) {
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/loopbutton.js');
        }
        $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/fixCurrentSources.js');

        $js .= "}";

        $js .= "});";
        //var_dump('getStartPlayerJSWasRequested', debug_backtrace());
        $getStartPlayerJSWasRequested = true;
        return $js;
    }

    static private function getCurrentTime()
    {
        $currentTime = 0;
        if (isset($_GET['t'])) {
            $currentTime = intval($_GET['t']);
        } else {
            $videos_id = getVideos_id();
            if (!empty($videos_id)) {
                $video = Video::getVideoLight($videos_id);
                if (!empty($video)) {
                    $progress = Video::getVideoPogressPercent($videos_id);
                    if (!empty($progress) && !empty($progress['lastVideoTime'])) {
                        $currentTime = intval($progress['lastVideoTime']);
                    } else if (!empty($video['externalOptions'])) {
                        $json = _json_decode($video['externalOptions']);
                        if (!empty($json->videoStartSeconds)) {
                            $currentTime = intval(parseDurationToSeconds($json->videoStartSeconds));
                        } else {
                            $currentTime = 0;
                        }
                    }
                    $maxCurrentTime = parseDurationToSeconds($video['duration']);
                    if ($maxCurrentTime <= $currentTime + 5) {
                        $currentTime = 0;
                    }
                } else {
                    return 0;
                }
            }
        }
        return $currentTime;
    }

    static function setIMAADTag($tag)
    {
        global $IMAADTag;
        $IMAADTag = $tag;
    }

    static function playerJSCodeOnLoad($videos_id, $nextURL = "")
    {
        $js = "";
        $videos_id = intval($videos_id);
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video("", "", $videos_id);
        if (!empty($video) && empty($nextURL)) {
            if (!empty($video->getNext_videos_id())) {
                $next_video = Video::getVideo($video->getNext_videos_id());
                if (!empty($next_video['id'])) {
                    $nextURL = Video::getURLFriendly($next_video['id'], isEmbed());
                }
            } else {
                $catName = @$_REQUEST['catName'];
                $cat = new Category($video->getCategories_id());
                $_REQUEST['catName'] = $cat->getClean_name();
                $next_video = Video::getVideo('', Video::SORT_TYPE_VIEWABLE, false, true);
                $_REQUEST['catName'] = $catName;
                if (!empty($next_video['id'])) {
                    $nextURL = Video::getURLFriendly($next_video['id'], isEmbed());
                }
            }
        }
        $url = Video::getURLFriendly($videos_id);
        $js .= "
        player.on('play', function () {
            sendAVideoMobileMessage('play', this.currentTime());
        });
        player.on('ended', function () {
            var time = Math.round(this.currentTime());
            sendAVideoMobileMessage('ended', time);
        });
        player.on('pause', function () {
            cancelAllPlaybackTimeouts();
            var time = Math.round(this.currentTime());
            sendAVideoMobileMessage('pause', time);
        });
        player.on('volumechange', function () {
            sendAVideoMobileMessage('volumechange', player.volume());
        });
        player.on('ratechange', function () {
            sendAVideoMobileMessage('ratechange', player.playbackRate);
        });
        player.on('timeupdate', function() {
            var time = Math.round(this.currentTime());
            playerCurrentTime = time;
            var url = '{$url}';

            if (url.indexOf('?') > -1) {
                url += '&t=' + time;
            } else {
                url += '?t=' + time;
            }

            $('#linkCurrentTime, .linkCurrentTime').val(url);

            sendAVideoMobileMessage('timeupdate', time);
        });
        ;";

        if (!empty($nextURL) && !isAVideoUserAgent()) {
            $js .= "playNextURL = '{$nextURL}';";
            $js .= "player.on('ended', function () {setTimeout(function(){if(playNextURL){playNext(playNextURL);}},playerHasAds()?10000:500);});";
        }
        self::getStartPlayerJS($js);
        return true;
    }

    static private function prepareStartPlayerJS($onPlayerReady = "", $getDataSetup = "")
    {
        global $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup;

        if (empty($prepareStartPlayerJS_onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady = array();
        }
        if (empty($prepareStartPlayerJS_getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup = array();
        }

        if (!empty($onPlayerReady)) {
            $prepareStartPlayerJS_onPlayerReady[] = $onPlayerReady;
        }
        if (!empty($getDataSetup)) {
            $prepareStartPlayerJS_getDataSetup[] = $getDataSetup;
        }
    }

    static function isAutoplayEnabled()
    {
        global $config;
        if (isLive()) {
            return true;
        }
        if (!empty($_COOKIE['autoplay'])) {
            if (strtolower($_COOKIE['autoplay']) === 'false') {
                return false;
            } else {
                return true;
            }
        }
        return $config->getAutoplay();
    }

    public static function getVideoTags($videos_id)
    {
        if (empty($videos_id)) {
            return array();
        }

        $cacheSuffix = 'PlayeSkins_getVideoTags';
        $videoCache = new VideoCacheHandler('', $videos_id);
        $tags = $videoCache->getCache($cacheSuffix, 0);

        //$name = "PlayeSkins_getVideoTags{$videos_id}";
        //$tags = ObjectYPT::getCache($name, 0);
        if (empty($tags)) {
            //_error_log("Cache not found $name");
            $video = new Video("", "", $videos_id, true);
            $fileName = $video->getFilename();
            //_error_log("getVideoTags($videos_id) $fileName ".$video->getType());
            $resolution = $video->getVideoHigestResolution();
            if (empty($resolution)) {
                $resolution = Video::getHigestResolution($fileName);
                if (!empty($resolution)) {
                    $video->setVideoHigestResolution($resolution);
                }
            } else {
                $resolution = Video::getResolutionArray($resolution);
            }

            $obj = new stdClass();
            if (empty($resolution) || empty($resolution['resolution_text'])) {
                $obj->label = '';
                $obj->type = "";
                $obj->text = "";
            } else {
                $obj->label = 'Plugin';
                $obj->type = "danger";
                $obj->text = $resolution['resolution_text'];
                $obj->tooltip = $resolution['resolution'] . 'p';
            }
            $tags = $obj;

            $videoCache->setCache($tags);
        }
        return array($tags);
    }

    /**
     *
     * @param array $markersList array(array('timeInSeconds'=>10,'name'=>'abc'),array('timeInSeconds'=>20,'name'=>'abc20'),array('timeInSeconds'=>25,'name'=>'abc25')....);
     * @param int $width
     * @param string $color
     */
    public static function createMarker($markersList, $width = 10, $color = 'yellow')
    {
        global $global;

        $bt = debug_backtrace();
        $file = str_replace($global['systemRootPath'], '', $bt[0]['file']);
        $onPlayerReady = '';
        $onPlayerReady .= " /* {$file} */
                player.markers({markerStyle: {
                    'width': '{$width}px',
                    'background-color': '{$color}'
                },
                markerTip: {
                    display: true,
                    text: function (marker) {
                        return marker.text;
                    }
                },
                markers: ";
        $markers = array();
        $addedSomething = false;
        foreach ($markersList as $value) {
            $obj = new stdClass();
            $obj->time = $value['timeInSeconds'];
            $obj->text = $value['name'];
            if (empty($obj->text)) {
                continue;
            }
            $addedSomething = true;
            $markers[] = $obj;
        }

        $onPlayerReady .= json_encode($markers);
        $onPlayerReady .= "});";
        if ($addedSomething) {
            self::$hasMarks = true;
            PlayerSkins::getStartPlayerJS($onPlayerReady);
        }
    }

    public function getWatchActionButton($videos_id)
    {
        global $global, $video;
        include $global['systemRootPath'] . 'plugin/PlayerSkins/actionButton.php';
    }

    public function getGalleryActionButton($videos_id)
    {
        global $global;
        include $global['systemRootPath'] . 'plugin/PlayerSkins/actionButtonGallery.php';
    }

    function executeEveryHour()
    {
        global $global;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        if ($obj->autoGenerateAndCacheEPG) {
            include "{$global['systemRootPath']}plugin/PlayerSkins/epg.php";
        }
    }
}
