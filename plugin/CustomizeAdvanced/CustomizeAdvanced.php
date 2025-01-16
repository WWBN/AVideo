<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class CustomizeAdvanced extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        $txt = "Fine Tuning your AVideo";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Advanced-Customization-Plugin' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "CustomizeAdvanced";
    }

    public function getUUID() {
        return "55a4fa56-8a30-48d4-a0fb-8aa6b3f69033";
    }

    public function getPluginVersion() {
        return "1.0";
    }
    
    public static function getDataObjectDeprecated() {
        return array(
            'EnableMinifyJS',
            'usePreloadLowResolutionImages',
            'useFFMPEGToGenerateThumbs',
            'makeVideosInactiveAfterEncode',
            'makeVideosUnlistedAfterEncode',
            'embedBackgroundColor',
            );
    }
    
    public static function getDataObjectExperimental() {
        return array(
            'autoPlayAjax',
        );
    }
    
    public static function getDataObjectAdvanced() {
        return array(
            'logoMenuBarURL',
            'encoderNetwork',
            'useEncoderNetworkRecomendation',
            'doNotShowEncoderNetwork',
            'doNotShowUploadButton',
            'uploadButtonDropdownIcon',
            'uploadButtonDropdownText',
            'encoderNetworkLabel',
            'doNotShowUploadMP4Button',
            'disablePDFUpload',
            'disableImageUpload',
            'disableZipUpload',
            'disableMP4Upload',
            'disableMP3Upload',
            'uploadMP4ButtonLabel',
            'doNotShowImportMP4Button',
            'importMP4ButtonLabel',
            'doNotShowEncoderButton',
            'encoderButtonLabel',
            'doNotShowEmbedButton',
            'embedButtonLabel',
            'embedCodeTemplate',
            'embedCodeTemplateObject',
            'htmlCodeTemplate',
            'BBCodeTemplate',
            'embedControls',
            'embedAutoplay',
            'embedLoop',
            'embedStartMuted',
            'embedShowinfo',
            'doNotShowEncoderHLS',
            'doNotShowEncoderResolutionLow',
            'doNotShowEncoderResolutionSD',
            'doNotShowEncoderResolutionHD',
            'openEncoderInIFrame',
            'showOnlyEncoderAutomaticResolutions',
            'doNotShowEncoderAutomaticHLS',
            'doNotShowEncoderAutomaticMP4',
            'doNotShowEncoderAutomaticWebm',
            'doNotShowEncoderAutomaticAudio',
            'saveOriginalVideoResolution',
            'doNotShowExtractAudio',
            'doNotShowCreateVideoSpectrum',
            'doNotShowLeftMenuAudioAndVideoButtons',
            'doNotShowWebsiteOnContactForm',
            'doNotUseXsendFile',
            'disableAnimatedGif',
            'doNotShowCreateVideoSpectrum',
            'disableShareAndPlaylist',
            'disableShareOnly',
            'disableEmailSharing',
            'splitBulkEmailSend',
            'commentsMaxLength',
            'commentsNoIndex',
            'disableYoutubePlayerIntegration',
            'utf8Encode',
            'utf8Decode',
            'menuBarHTMLCode',
            'underMenuBarHTMLCode',
            'footerHTMLCode',
            'signInOnRight',
            'signInOnLeft',
            'forceCategory',
            'showCategoryTopImages',
            'disablePlayLink',
            'disableAnimations',
            'disableNavbar',
            'disableNavBarInsideIframe',
            'autoHideNavbar',
            'autoHideNavbarInSeconds',
            'videosCDN',
            'thumbsWidthPortrait',
            'thumbsHeightPortrait',
            'thumbsWidthLandscape',
            'thumbsHeightLandscape',
            'showImageDownloadOption',
            'doNotDisplayCategoryLeftMenu',
            'doNotDisplayCategory',
            'showShareMenuOpenByDefault',
            'doNotShowLeftHomeButton',
            'doNotShowLeftTrendingButton',
            'CategoryLabel',
            'ShowAllVideosOnCategory',
            'hideCategoryVideosCount',
            'categoryLiveCount',
            'hideEditAdvancedFromVideosManager',
            'paidOnlyUsersTellWhatVideoIs',
            'paidOnlyShowLabels',
            'paidOnlyLabel',
            'paidOnlyFreeLabel',
            'removeSubscribeButton',
            'siteMapRowsLimit',
            'siteMapUTF8Fix',
            'showPrivateVideosOnSitemap',
            'disableSiteMapVideoDescription',
            'enableOldPassHashCheck',
            'disableShowMOreLessDescription',
            'disableVideoSwap',
            'makeSwapVideosOnlyForAdmin',
            'videosManegerRowCount',
            'videosListRowCount',
            'videosManegerBulkActionButtons',
            'twitter_site',
            'twitter_player',
            'twitter_summary_large_image',
            'footerStyle',
            'doNotAllowEncoderOverwriteStatus',
            'doNotAllowUpdateVideoId',
            'doNotSaveCacheOnFilesystem',
            'beforeNavbar',
            'trendingOnLastDays',
            'removeVideoList',
            'sortVideoListByDefault',
            'showVideoDownloadedLink',
            'showEllipsisMenuOnVideoItem',
            'showCreationTimeOnVideoItem',
            'showChannelPhotoOnVideoItem',
            'showChannelNameOnVideoItem',
            'canonicalURLType',
            'ffmpegParameters',
            );
    }
    
    public function getEmptyDataObject() {
        global $global, $statusThatTheUserCanUpdate, $advancedCustom;
        $obj = new stdClass();
        $obj->enableVideoModeration = false;
        self::addDataObjectHelper('enableVideoModeration', 'Video moderation', 'When enabled, leaves all videos unpublished. Only the administrator and video moderators have the authority to activate and make these videos public');
        $obj->logoMenuBarURL = "";
        $obj->encoderNetwork = "https://network.wwbn.net/";
        $obj->useEncoderNetworkRecomendation = false;
        $obj->doNotShowEncoderNetwork = true;
        $obj->doNotShowUploadButton = false;
        $obj->uploadButtonDropdownIcon = "fas fa-video";
        $obj->uploadButtonDropdownText = "";
        $obj->encoderNetworkLabel = "";
        $obj->doNotShowUploadMP4Button = true;
        self::addDataObjectHelper('doNotShowUploadMP4Button', 'Disable direct upload', __('Users will not be able to directly upload, only use the encoder'));
        $obj->disablePDFUpload = false;
        $obj->disableImageUpload = false;
        $obj->disableZipUpload = true;
        $obj->disableMP4Upload = false;
        $obj->disableMP3Upload = false;
        $obj->uploadMP4ButtonLabel = "";
        $obj->doNotShowImportMP4Button = true;
        $obj->importMP4ButtonLabel = "";
        $obj->doNotShowEncoderButton = false;
        $obj->encoderButtonLabel = "";
        $obj->doNotShowEmbedButton = false;
        $obj->embedBackgroundColor = "white";
        $obj->embedButtonLabel = "";
        $obj->embedCodeTemplate = '<div class="embed-responsive embed-responsive-16by9" style="position: relative;padding-bottom: 56.25% !important;"><iframe width="640" height="360" style="max-width: 100%;max-height: 100%; border:none;position: absolute;top: 0;left: 0;width: 100%; height: 100%;" src="{embedURL}" frameborder="0" '.Video::$iframeAllowAttributes.' scrolling="no" videoLengthInSeconds="{videoLengthInSeconds}">iFrame is not supported!</iframe></div>';
        $obj->embedCodeTemplateObject = '<div class="embed-responsive embed-responsive-16by9"><object width="640" height="360"><param name="movie" value="{embedURL}"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="{embedURL}" allowscriptaccess="always" allowfullscreen="true" width="640" height="360"></embed></object></div>';
        $obj->htmlCodeTemplate = '<a href="{permaLink}"><img src="{imgSRC}">{title}</a>';
        $obj->BBCodeTemplate = '[url={permaLink}][img]{imgSRC}[/img]{title}[/url]';

        $o = new stdClass();
        $o->type = array(-1 => __("Basic Controls"), 0 => __("No Controls"), 1 => __("All controls"));
        $o->value = 1;
        $obj->embedControls = $o;
        $obj->embedAutoplay = false;
        $obj->embedLoop = false;
        $obj->embedStartMuted = false;
        $obj->embedShowinfo = true;

        $obj->doNotShowEncoderHLS = false;
        $obj->doNotShowEncoderResolutionLow = false;
        $obj->doNotShowEncoderResolutionSD = false;
        $obj->doNotShowEncoderResolutionHD = false;
        $obj->openEncoderInIFrame = false;
        $obj->showOnlyEncoderAutomaticResolutions = true;
        $obj->doNotShowEncoderAutomaticHLS = false;
        $obj->doNotShowEncoderAutomaticMP4 = false;
        $obj->doNotShowEncoderAutomaticWebm = false;
        $obj->doNotShowEncoderAutomaticAudio = false;

        $o = new stdClass();
        $o->type = array(0 => __('Disabled'), 240 => __('Max 240p'), 360 => __('Max 360p'), 480 => __('Max 480p'), 540 => __('Max 540p'), 720 => __('Max 720p'), 1080 => __('Max 1080p'), 1440 => __('Max 1440p'), 2160 => __('Max 2160p'));
        $o->value = 0;
        $obj->singleResolution = $o;
        self::addDataObjectHelper(
            'singleResolution',
            'Save MP4 videos in a single resolution',
            'Select the maximum resolution to save each video. Videos will not be upscaled to higher resolutions (Must enable automatic resolutions)'
        );
        $obj->saveOriginalVideoResolution = false;
        self::addDataObjectHelper('saveOriginalVideoResolution', 'Save the original video resolution', 'This option will make your encoder at the end trancode the video into the original format resolution');
        $obj->doNotShowExtractAudio = false;
        $obj->doNotShowCreateVideoSpectrum = false;
        $obj->doNotShowLeftMenuAudioAndVideoButtons = false;
        $obj->doNotShowWebsiteOnContactForm = false;
        $obj->doNotUseXsendFile = false;
        $obj->makeVideosInactiveAfterEncode = false;
        $obj->makeVideosUnlistedAfterEncode = false;
        
        $o = new stdClass();
        $o->type = array();
        if(empty($statusThatTheUserCanUpdate)){
            $statusThatTheUserCanUpdate = array();
        }
        foreach ($statusThatTheUserCanUpdate as $value) {
            $statusIndex = $value[0];
            $statusColor = $value[1];
            $o->type[$statusIndex] = Video::$statusDesc[$statusIndex];
        }
        
        $dbObject = PluginAbstract::getObjectDataFromDatabase($this->getUUID());
        
        if (!empty($dbObject->makeVideosInactiveAfterEncode)) {
            $o->value = Video::$statusInactive;
        } elseif (!empty($dbObject->makeVideosUnlistedAfterEncode)) {
            $o->value = Video::$statusUnlisted;
        }else{
            $o->value = Video::$statusActive;
        }
        $obj->defaultVideoStatus = $o;
        self::addDataObjectHelper('defaultVideoStatus', 'Default video status', 'When you submit a video that will be the default status');
        
        $obj->usePermalinks = false;
        self::addDataObjectHelper('usePermalinks', 'Do not show video title on URL', 'This option is not good for SEO, but makes the URL clear');
              
        $o = new stdClass();  
        $o->type = array(0 => 'Short URL', 1 => 'URL+Channel Name', 2 => 'URL+Channel+Title');
        $o->value = 1;
        $obj->canonicalURLType = $o;
        
        $obj->disableAnimatedGif = false;
        $obj->removeBrowserChannelLinkFromMenu = false;
        $obj->EnableMinifyJS = false;
        $obj->disableShareAndPlaylist = false;
        $obj->disableShareOnly = false;
        $obj->disableEmailSharing = false;
        $obj->splitBulkEmailSend = 50;
        $obj->disableComments = false;
        $obj->commentsMaxLength = 200;
        $obj->commentsNoIndex = false;
        $obj->disableYoutubePlayerIntegration = false;
        $obj->utf8Encode = false;
        $obj->utf8Decode = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->menuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->underMenuBarHTMLCode = $o;
        $o->type = "textarea";
        $o->value = "";
        $obj->footerHTMLCode = $o;
        $obj->signInOnRight = true;
        $obj->signInOnLeft = true;
        $obj->forceCategory = false;
        $obj->showCategoryTopImages = true;
        $obj->autoPlayAjax = false;

        $plugins = Plugin::getAllEnabled();
        //import external plugins configuration options
        foreach ($plugins as $value) {
            $p = AVideoPlugin::loadPlugin($value['dirName']);
            if (is_object($p)) {
                $foreginObjects = $p->getCustomizeAdvancedOptions();
                if ($foreginObjects) {
                    foreach ($foreginObjects as $optionName => $defaultValue)
                        $obj->{$optionName} = $defaultValue;
                }
            }
        }

        $obj->disableInstallPWAButton = false;
        $obj->disablePlayLink = false;
        $obj->disableHelpLeftMenu = false;
        $obj->disableAboutLeftMenu = false;
        $obj->disableContactLeftMenu = false;
        $obj->disableAnimations = false;
        $obj->disableNavbar = false;
        $obj->disableNavBarInsideIframe = true;
        $obj->autoHideNavbar = true;
        $obj->autoHideNavbarInSeconds = 0;
        $obj->videosCDN = "";
        $obj->useFFMPEGToGenerateThumbs = false;
        $obj->thumbsWidthPortrait = 170;
        $obj->thumbsHeightPortrait = 250;
        $obj->thumbsWidthLandscape = 640;
        $obj->thumbsHeightLandscape = 360;
        $obj->usePreloadLowResolutionImages = false;
        $obj->showImageDownloadOption = false;
        $obj->doNotDisplayViews = false;
        $obj->doNotDisplayLikes = false;
        $obj->doNotDisplayCategoryLeftMenu = false;
        $obj->doNotDisplayCategory = false;
        $obj->doNotDisplayGroupsTags = false;
        $obj->doNotDisplayPluginsTags = false;
        $obj->showNotRatedLabel = false;
        $obj->showShareMenuOpenByDefault = false;
        $obj->showEllipsisMenuOnVideoItem = true;
        $obj->showCreationTimeOnVideoItem = true;
        $obj->showChannelPhotoOnVideoItem = true;
        $obj->showChannelNameOnVideoItem = true;
        /**
         * @var mixed[] $global
         */
        foreach ($global['social_medias'] as $key => $value) {
            eval("\$obj->showShareButton_{$key} = true;");
        }

        $obj->askRRatingConfirmationBeforePlay_G = false;
        $obj->askRRatingConfirmationBeforePlay_PG = false;
        $obj->askRRatingConfirmationBeforePlay_PG13 = false;
        $obj->askRRatingConfirmationBeforePlay_R = false;
        $obj->askRRatingConfirmationBeforePlay_NC17 = true;
        $obj->askRRatingConfirmationBeforePlay_MA = true;
        $obj->filterRRating = false;

        $obj->doNotShowLeftHomeButton = false;
        $obj->doNotShowLeftTrendingButton = false;

        $obj->CategoryLabel = "Categories";
        $obj->ShowAllVideosOnCategory = false;
        $obj->hideCategoryVideosCount = false;
        $obj->hideEditAdvancedFromVideosManager = false;
        $obj->categoryLiveCount = true;

        //ver 7.1
        $obj->paidOnlyUsersTellWhatVideoIs = false;
        self::addDataObjectHelper('paidOnlyUsersTellWhatVideoIs', 'Only Paid Users Can see option', 'This will create an option on videos manager to only let paid users to see this video');
        
        $obj->paidOnlyShowLabels = false;
        $obj->paidOnlyLabel = "Premium";
        $obj->paidOnlyFreeLabel = "Free";
        $obj->removeSubscribeButton = false;
        $obj->removeThumbsUpAndDown = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->videoNotFoundText = $o;
        $obj->siteMapRowsLimit = 100;
        $obj->siteMapUTF8Fix = false;
        $obj->showPrivateVideosOnSitemap = false;
        $obj->disableSiteMapVideoDescription = false;
        $obj->enableOldPassHashCheck = true;
        $obj->disableHTMLDescription = false;
        $obj->disableShowMOreLessDescription = false;
        $obj->disableVideoSwap = false;
        $obj->makeSwapVideosOnlyForAdmin = false;
        $obj->disableCopyEmbed = false;
        $obj->disableDownloadVideosList = false;
        $obj->videosManegerRowCount = "[10, 25, 50, -1]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.
        $obj->videosListRowCount = "[10, 20, 30, 40, 50]"; //An Array of Integer which will be shown in the dropdown box to choose the row count. Default value is [10, 25, 50, -1]. -1 means all. When passing an Integer value the dropdown box will disapear.
        $obj->videosManegerBulkActionButtons = true;

        $parse = parse_url($global['webSiteRootURL']);
        $domain = str_replace(".", "", $parse['host']);
        $obj->twitter_site = "@{$domain}";
        $obj->twitter_player = true;
        $obj->twitter_summary_large_image = false;
        $obj->footerStyle = "position: fixed;bottom: 0;width: 100%;";
        $obj->disableVideoTags = false;
        $obj->doNotAllowEncoderOverwriteStatus = false;
        $obj->doNotAllowUpdateVideoId = false;
        $obj->makeVideosIDHarderToGuess = false;
        self::addDataObjectHelper('makeVideosIDHarderToGuess', 'Make the videos ID harder to guess', 'This will change the videos_id on the URL to a crypted value. this crypt user your $global[salt] (configuration.php), so make sure you keep it save in case you need to restore your site, otherwise all the shared links will be lost');

        $o = new stdClass();
        $o->type = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $o->value = 0;
        $obj->timeZone = $o;

        $obj->keywords = "AVideo, videos, live, movies";
        $obj->doNotSaveCacheOnFilesystem = false;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "Allow: /plugin/Live/?*
Allow: /plugin/PlayLists/*.css
Allow: /plugin/PlayLists/*.js
Allow: /plugin/TopMenu/*.css
Allow: /plugin/TopMenu/*.js
Allow: /plugin/SubtitleSwitcher/*.css
Allow: /plugin/SubtitleSwitcher/*.js
Allow: /plugin/Gallery/*.css
Allow: /plugin/Gallery/*.js
Allow: /plugin/YouPHPFlix2/*.png
Allow: /plugin/Live/*.css
Allow: /plugin/Live/*.js
Allow: /plugin/LiveLink/?*
Allow: /plugin/*.css
Allow: /plugin/*.js
Allow: *.js
Allow: *.css
Disallow: /user
Disallow: /plugin
Disallow: /mvideos
Disallow: /usersGroups
Disallow: /charts
Disallow: /upload
Disallow: /comments
Disallow: /subscribes
Disallow: /update
Disallow: /locale
Disallow: /objects/*
Disallow: /view/*
Disallow: /page/*
Disallow: *?lang=*
Disallow: *&lang=*
Disallow: *handler=search*
Disallow: *action=tagsearch*
";
        $obj->robotsTXT = $o;
        self::addDataObjectHelper('robotsTXT', 'robots.txt file content', 'robots.txt is a plain text file that follows the Robots Exclusion Standard. A robots.txt file consists of one or more rules. Each rule blocks (or allows) access for a given crawler to a specified file path in that website.');
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->beforeNavbar = $o;
        self::addDataObjectHelper('beforeNavbar', 'Add some code before the navbar HTML');
        
        
        $o = new stdClass();
        $o->type = array(1 => __("1 Day"), 10 => __("10 Days"), 15 => __("15 Days"), 20 => __("20 Days"), 25 => __("25 Days"), 30 => __("30 Days"), 60 => __("60 Days"));
        $o->value = 30;
        $obj->trendingOnLastDays = $o;
        self::addDataObjectHelper('trendingOnLastDays', 'Trending Days', 'For the result of trending videos, use the statistics contained in the last few days');

        
        $obj->removeVideoList = false;
        $o = new stdClass();
        $o->type = array(
            'titleAZ' => __("Title (A-Z)"), 
            'titleZA' => __("Title (Z-A)"), 
            'newest' => __("Date added (newest)"), 
            'oldest' => __("Date added (oldest)"), 
            'popular' => __("Most popular"), 
            'views_count' => __("Most watched"), 
            'suggested' => __("Suggested"), 
            'trending' => __("Trending")
            );
        $o->value = 'newest';
        $obj->sortVideoListByDefault = $o;
        self::addDataObjectHelper('sortVideoListByDefault', 'Sort Video List By Default');
        
        $obj->showVideoDownloadedLink = false;
        self::addDataObjectHelper('showVideoDownloadedLink', 'Show video Downloaded Link', 'Show the video source URL above the video description');
        
        $obj->videosForKids = true;


        $obj->autoConvertVideosToMP3 = false;
        $obj->allowDownloadMP3 = true;

        $obj->disableFeeds = false;

        $obj->ffmpegParameters = "-c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k";   
        
        return $obj;
    }
    
    static function showDirectUploadButton(){
        global $_showDirectUploadButton;        
        if(!isset($_showDirectUploadButton)){        
            $obj = AVideoPlugin::getDataObject('CustomizeAdvanced');
            if(empty($obj->doNotShowUploadMP4Button)){
                if(!empty(self::directUploadFiletypes())){
                    $_showDirectUploadButton = true;
                }else{
                    $_showDirectUploadButton = false;
                }
            }else{               
                $_showDirectUploadButton = false;
            }
        }
        return $_showDirectUploadButton;
    }
    
    static function directUploadFiletypes(){
        global $_directUploadFiletypes;        
        if(!isset($_directUploadFiletypes)){  
            $_directUploadFiletypes = array();
            $obj = AVideoPlugin::getDataObject('CustomizeAdvanced');
            if(empty($obj->disablePDFUpload)){
                $_directUploadFiletypes[] = 'pdf';
            }
            if(empty($obj->disableImageUpload)){
                $_directUploadFiletypes[] = 'images';
            }
            if(empty($obj->disableZipUpload)){
                $_directUploadFiletypes[] = 'zip';
            }
            if(empty($obj->disableMP4Upload)){
                $_directUploadFiletypes[] = 'mp4';
            }
            if(empty($obj->disableMP3Upload)){
                $_directUploadFiletypes[] = 'mp3';
            }
        }
        return $_directUploadFiletypes;
    }
    
    public function navBar() {
        $obj = $this->getDataObject();
        $str = '';
        if(!emptyHTML($obj->beforeNavbar->value)){
            $str .= $obj->beforeNavbar->value;
        }
        return $str;
    }

    public function getHelp() {
        if (User::isAdmin()) {
            return "<h2 id='CustomizeAdvanced help'>CustomizeAdvanced (admin)</h2><p>" . $this->getDescription() . "</p>";
        }
        return "";
    }

    public function getModeYouTube($videos_id) {
        global $global, $config;
        
        $redirectVideo = self::getRedirectVideo($videos_id);
        //var_dump($redirectVideo);exit;
        if(!empty($redirectVideo) && !empty($redirectVideo->code) && isValidURL($redirectVideo->url) && getSelfURI() !== $redirectVideo->url){
            header("Location: {$redirectVideo->url}", true, $redirectVideo->code);
            exit;
        }
        
        $obj = $this->getDataObject();
        
        self::createMP3($videos_id);

        $video = Video::getVideo($videos_id, Video::SORT_TYPE_VIEWABLE, true);
        if (!empty($video['rrating']) && empty($_GET['rrating'])) {
            $suffix = strtoupper(str_replace("-", "", $video['rrating']));
            eval("\$show = \$obj->askRRatingConfirmationBeforePlay_$suffix;");
            if (!empty($show)) {
                include "{$global['systemRootPath']}plugin/CustomizeAdvanced/confirmRating.php";
                exit;
            }
        }
    }

    public function getEmbed($videos_id) {
        return $this->getModeYouTube($videos_id);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if ($obj->autoHideNavbar && !isEmbed()) {
            $content .= '<script>$(function () {setTimeout(function(){if(typeof $("#mainNavBar").autoHidingNavbar == "function"){$("#mainNavBar").autoHidingNavbar();}},5000);});</script>';
            $content .= '<script>' . file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbar.js') . '</script>';
        }
        if ($obj->autoHideNavbarInSeconds && !isEmbed()) {
            $content .= '<script>'
                    . 'var autoHidingNavbarTimeoutMiliseconds = ' . intval($obj->autoHideNavbarInSeconds * 1000) . ';'
                    . file_get_contents($global['systemRootPath'] . 'plugin/CustomizeAdvanced/autoHideNavbarInSeconds.js')
                    . '</script>';
        }
        $content .= '<script>iframeAllowAttributes = \''. (Video::$iframeAllowAttributes).'\';</script>';
        return $content;
    }

    public function getHTMLMenuRight() {
        global $global, $config,$advancedCustom;
        $obj = $this->getDataObject();
        if (!empty($obj->menuBarHTMLCode->value)) {
            echo $obj->menuBarHTMLCode->value;
        }
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuRight.php';
        }
        if (User::canUpload() && empty($obj->doNotShowUploadButton)) {
            include $global['systemRootPath'] . 'view/include/navbarUpload.php';
        } else {
            include $global['systemRootPath'] . 'view/include/navbarNotUpload.php';
        }
    }

    public function getHTMLMenuLeft() {
        global $global;
        $obj = $this->getDataObject();
        if ($obj->filterRRating) {
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/menuLeft.php';
        }
    }

    public static function getVideoWhereClause() {
        $sql = "";
        $obj = AVideoPlugin::getObjectData("CustomizeAdvanced");
        if ($obj->filterRRating && isset($_GET['rrating'])) {
            if ($_GET['rrating'] === "0") {
                $sql .= " AND v.rrating = ''";
            } else if (in_array($_GET['rrating'], Video::$rratingOptions)) {
                $sql .= " AND v.rrating = '{$_GET['rrating']}'";
            }
        }
        return $sql;
    }

    public function getVideosManagerListButton() {
        $btn = "";
        if (User::isAdmin()) {
            $btn = '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="updateDiskUsage(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __("Update disk usage for this media") . '"><i class="fas fa-chart-line"></i> ' . __("Update Disk Usage") . '</button>';
            $btn .= '<button type="button" class="btn btn-default btn-light btn-sm btn-xs btn-block " onclick="removeThumbs(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="' . __("Remove thumbs for this media") . '"><i class="fas fa-images"></i> ' . __("Remove Thumbs") . '</button>';
        }
        return $btn;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();

        if ($obj->makeVideosIDHarderToGuess) {
            if (isVideo()) {
                if (!empty($global['makeVideosIDHarderToGuessNotDecrypted'])) {
                    unset($global['makeVideosIDHarderToGuessNotDecrypted']);
                    forbiddenPage(__('Invalid ID'));
                }
            }
        }

        $baseName = basename($_SERVER['REQUEST_URI']);

        $js = "";
        if (empty($obj->autoPlayAjax)) {
            $js .= "<script>var autoPlayAjax=false;</script>";
        } else {
            $js .= "<script>var autoPlayAjax=true;</script>";
        }
        if ($baseName === 'mvideos') {
            $js .= '<script src="'.getURL('plugin/CustomizeAdvanced/mvideo.script.js').'" type="text/javascript"></script>';
        }
        return $js;
    }

    public function onReceiveFile($videos_id) {
        Video::updateFilesize($videos_id);
        return true;
    }

    public function afterNewVideo($videos_id) {
        self::createMP3($videos_id);
        return false;
    }
    
    public static function createMP3($videos_id){
        $obj = AVideoPlugin::getDataObject('CustomizeAdvanced');
        if($obj->autoConvertVideosToMP3){
            convertVideoToMP3FileIfNotExists($videos_id);
        }
    }

    public static function getManagerVideosAddNew() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CustomizeAdvanced/getManagerVideosAddNew.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEdit() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CustomizeAdvanced/getManagerVideosEdit.js';
        return file_get_contents($filename);
    }

    public static function getManagerVideosEditField($type='Advanced') {
        global $global;
        if($type == 'Advanced'){
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/managerVideosEdit.php';
        }else if($type == 'SEO'){
            include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/managerVideosEditSEO.php';
        }
        return '';
    }

    public static function saveVideosAddNew($post, $videos_id) {
        if(isset($post['doNotShowAdsOnThisChannel'])){
            self::setDoNotShowAdsOnChannel($videos_id, !_empty($post['doNotShowAdsOnThisChannel']));
        }
        if(isset($post['doNotShowAdsOnThisVideo'])){
            self::setDoNotShowAds($videos_id, !_empty($post['doNotShowAdsOnThisVideo']));
        }
        if(isset($post['redirectVideoCode'])){
            self::setRedirectVideo($videos_id, @$post['redirectVideoCode'], @$post['redirectVideoURL']);
        }
        if(isset($post['ShortSummary'])){
            self::setShortSummaryAndMetaDescriptionVideo($videos_id,@$post['ShortSummary'], @$post['MetaDescription']);
        }
    }
    
    public static function setDoNotShowAds($videos_id, $doNotShowAdsOnThisVideo) {
        if (!Permissions::canAdminVideos()) {
            return false;
        }
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        if(!isset($externalOptions)){
            $externalOptions = new stdClass();
        }
        $externalOptions->doNotShowAdsOnThisVideo = $doNotShowAdsOnThisVideo;
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }
    
    public static function setDoNotShowAdsOnChannel($videos_id, $doNotShowAdsOnThisChannel) {
        if (!Permissions::canAdminVideos()) {
            return false;
        }
        $video = new Video('', '', $videos_id, true);
        $users_id = $video->getUsers_id();
        $user = new User($users_id);
        return $user->addExternalOptions('doNotShowAdsOnThisChannel', $doNotShowAdsOnThisChannel);
    }

    public static function getDoNotShowAds($videos_id): bool {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return !empty($externalOptions->doNotShowAdsOnThisVideo);
    }
    
    public static function getDoNotShowAdsChannel($videos_id): bool {
        $video = new Video('', '', $videos_id);
        $users_id = $video->getUsers_id();
        $user = new User($users_id);
        $externalOptions = object_to_array(_json_decode(User::decodeExternalOption($user->_getExternalOptions())));
        return !empty($externalOptions['doNotShowAdsOnThisChannel']);
    }
    
    public static function setRedirectVideo($videos_id, $code, $url) {
        if (!Permissions::canAdminVideos()) {
            return false;
        }
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        if(!is_object($externalOptions)){
            $externalOptions = new stdClass();
        }
        $externalOptions->redirectVideo = array('code'=>$code, 'url'=>$url);
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    public static function getRedirectVideo($videos_id) {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return @$externalOptions->redirectVideo;
    }
    
    public static function setShortSummaryAndMetaDescriptionVideo($videos_id, $ShortSummary, $MetaDescription) {
        if (!Video::canEdit($videos_id)) {
            return false;
        }
        $video = new Video('', '', $videos_id, true);
        $externalOptions = _json_decode($video->getExternalOptions());
        if(empty($externalOptions)){
            $externalOptions = new stdClass();
        }
        $externalOptions->SEO = array('ShortSummary'=>$ShortSummary, 'MetaDescription'=>$MetaDescription);
        $video->setExternalOptions(json_encode($externalOptions));
        return $video->save();
    }

    public static function getShortSummaryAndMetaDescriptionVideo($videos_id) {
        $video = new Video('', '', $videos_id);
        $externalOptions = _json_decode($video->getExternalOptions());
        return @$externalOptions->SEO;
    }
    
    public function showAds($videos_id): bool {
        return !self::getDoNotShowAdsChannel($videos_id) && !self::getDoNotShowAds($videos_id);
    }
    
    public function getGalleryActionButton($videos_id) {
        global $global;
        include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/actionButtonGallery.php';
    }

    public function getWatchActionButton($videos_id) {
        global $global, $video;
        include $global['systemRootPath'] . 'plugin/CustomizeAdvanced/actionButton.php';
    }

    static function autoConvert()
    {
        global $global;
        $sql = "SELECT * FROM  videos WHERE `type` = '" . Video::$videoTypeVideo . "' ORDER BY id DESC ";
        $res = sqlDAL::readSql($sql, "", [], true);
        $fullData = sqlDAL::fetchAllAssoc($res);
        sqlDAL::close($res);
        $transferStatus = [];
        $transferStatus[] = Video::$statusActive;
        $transferStatus[] = Video::$statusFansOnly;
        $transferStatus[] = Video::$statusScheduledReleaseDate;
        $transferStatus[] = Video::$statusUnlisted;
        $transferStatus[] = Video::$statusUnlistedButSearchable;

        if ($res != false) {
            foreach ($fullData as $key => $row) {
                if (in_array($row['status'], $transferStatus)) {
                    $folderPath = "{$global['systemRootPath']}videos/{$row['filename']}/";
                    $mp3 = findMP3File($folderPath);
                    if (!empty($mp3)) {
                        _error_log("CustomizeAdvanced::autoConvert {$row['id']} to MP4 $folderPath");
                        $resp = self::createMP3($row['id']);
                    } else {
                        //_error_log('VideoHLS::autoConvert to MP4 (already converted) '.$row['id']." $mp4");
                    }
                } else {
                    //_error_log("VideoHLS::autoConvert to MP4 (wrong status [{$row['status']}]) ".$row['id']);
                }
            }
        }
    }


    function executeEveryHour() {
        $obj = AVideoPlugin::getDataObject('CustomizeAdvanced');
        if($obj->autoConvertVideosToMP3){
            self::autoConvert();
        }
    }
}

class SocialMedias {

    public $href;
    public $class;
    public $title;
    public $iclass;
    public $img;
    public $onclick;

    function __construct($iclass, $img = '') {
        $this->iclass = $iclass;
        $this->img = $img;
    }

    function getHref() {
        return $this->href;
    }

    function getClass() {
        return $this->class;
    }

    function getTitle() {
        return $this->title;
    }

    function getIclass() {
        return $this->iclass;
    }

    function getImg() {
        return $this->img;
    }

    function getOnclick() {
        return $this->onclick;
    }

    function setHref($href): void {
        $this->href = $href;
    }

    function setClass($class): void {
        $this->class = $class;
    }

    function setTitle($title): void {
        $this->title = $title;
    }

    function setIclass($iclass): void {
        $this->iclass = $iclass;
    }

    function setImg($img): void {
        $this->img = $img;
    }

    function setOnclick($onclick): void {
        $this->onclick = $onclick;
    }
    
}

$global['social_medias'] = array(
    'Whatsapp' => new SocialMedias('fab fa-whatsapp', ''),
    'Telegram' => new SocialMedias('fab fa-telegram-plane', ''),
    'Facebook' => new SocialMedias('fab fa-facebook-square', ''),
    'Twitter' => new SocialMedias('fa-brands fa-x-twitter', ''),
    'Tumblr' => new SocialMedias('fab fa-tumblr', ''),
    'Pinterest' => new SocialMedias('fab fa-pinterest-p', ''),
    'Reddit' => new SocialMedias('fab fa-reddit-alien', ''),
    'LinkedIn' => new SocialMedias('fab fa-linkedin-in', ''),
    'Wordpress' => new SocialMedias('fab fa-wordpress-simple', ''),
    'Pinboard' => new SocialMedias('fas fa-thumbtack', ''),
    'Gab' => new SocialMedias('', getURL('view/img/social/gab.png')),
    'CloutHub' => new SocialMedias('', getURL('view/img/social/cloutHub.png')),
);
