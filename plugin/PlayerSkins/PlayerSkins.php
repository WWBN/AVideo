<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

class PlayerSkins extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription() {
        global $global;
        $desc = "Customize your playes Skin<br>The Skis options are: ";
        $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
        $names = array();
        foreach (glob($dir . '*.css') as $file) {
            $path_parts = pathinfo($file);
            $names[] = $path_parts['filename'];
        }
        return $desc . "<code>" . implode("</code> or <code>", $names) . "</code>";
    }

    public function getName() {
        return "PlayerSkins";
    }

    public function getUUID() {
        return "e9a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->skin = "avideo";
        $obj->playbackRates = "[0.5, 1, 1.5, 2]";
        $obj->playerCustomDataSetup = "";
        $obj->showSocialShareOnEmbed = true;
        $obj->showLoopButton = true;
        $obj->showLogo = false;
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
        return $obj;
    }

    public function getHeadCode() {
        global $global, $config;
        $obj = $this->getDataObject();
        $css = "";
        $js = "";
        if(isLive()){
            $js .= "<script>var isLive = true;</script>";
        }
        if (isVideo() || !empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            if (self::isAutoplayEnabled()) {
                $js .= "<script>var autoplay = true;</script>";
            } else {
                $js .= "<script>var autoplay = false;</script>";
            }
            $js .= "<script>var playNextURL = '';</script>";
            if(!empty($obj->skin)){
                $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/skins/{$obj->skin}.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if ($obj->showLoopButton && !isLive()) {
                $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/loopbutton.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/player.css\" rel=\"stylesheet\" type=\"text/css\"/>";
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
    
}"
                        . "</style>";
            }
        }
        
        $url = urlencode(getSelfURI());
        $oembed = '<link href="'.$global['webSiteRootURL'].'oembed/?format=json&url='.$url.'" rel="alternate" type="application/json+oembed" />';
        $oembed .= '<link href="'.$global['webSiteRootURL'].'oembed/?format=xml&url='.$url.'" rel="alternate" type="application/xml+oembed" />';
        
        
        return $js.$css.$oembed;
    }

    public function getFooterCode() {
        global $global, $config, $getStartPlayerJSWasRequested;
        $js = "<!-- playerSkin -->";
        $obj = $this->getDataObject();
        if (!empty($_GET['videoName']) || !empty($_GET['u']) || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            if (empty($obj->showLoopButton) && empty($playerSkinsObj->contextMenuLoop)) {
                $js .= "<script>setPlayerLoop(false);</script>";
            }
            if ($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo) {
                $title = $config->getWebSiteTitle();
                $url = "{$global['webSiteRootURL']}{$config->getLogo(true)}";
                $js .= "<script>var PlayerSkinLogoTitle = '{$title}';</script>";
                $js .= "<script src=\"{$global['webSiteRootURL']}plugin/PlayerSkins/logo.js\"></script>";

                PlayerSkins::getStartPlayerJS("if (player.getChild('controlBar').getChild('PictureInPictureToggle')) {
    player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('PictureInPictureToggle') + 1);
} else {
    player.getChild('controlBar').addChild('Logo', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
}");
            }
        }
        if (!empty($getStartPlayerJSWasRequested) || isVideo()) {
            $js .= "<script src=\"{$global['webSiteRootURL']}view/js/videojs-persistvolume/videojs.persistvolume.js\"></script>";
            $js .= "<script>" . self::getStartPlayerJSCode() . "</script>";
        }

        return $js;
    }

    static function getDataSetup($str = "") {
        global $video, $disableYoutubeIntegration, $global;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');

        $dataSetup = array();

        $dataSetup[] = "errorDisplay: false";
        if (!isLive() && !empty($obj->playbackRates)) {
            $dataSetup[] = "'playbackRates':{$obj->playbackRates}";
        }
        if (!isLive() && (isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false) && !empty($video['videoLink'])) {
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
    static function getStartPlayerJS($onPlayerReady = "", $getDataSetup = "", $noReadyFunction = false) {
        global $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup;
        global $getStartPlayerJSWasRequested;
        self::prepareStartPlayerJS($onPlayerReady, $getDataSetup);
        $getStartPlayerJSWasRequested = true;
        //return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '", $onPlayerReady = "' . $onPlayerReady . '", $getDataSetup = "' . $getDataSetup . '" */';
        return '/* getStartPlayerJS $prepareStartPlayerJS_onPlayerReady = "' . count($prepareStartPlayerJS_onPlayerReady) . '", $prepareStartPlayerJS_getDataSetup = "' . count($prepareStartPlayerJS_getDataSetup) . '" */';
    }

    static function getStartPlayerJSCode($noReadyFunction = false, $currentTime = 0) {
        global $config, $global, $prepareStartPlayerJS_onPlayerReady, $prepareStartPlayerJS_getDataSetup, $IMAADTag;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        $js = "";
        if (empty($noReadyFunction)) {
            $js .= "var originalVideo; "
                    . "$(document).ready(function () {";
        }
        $js .= "
        originalVideo = $('#mainVideo').clone();
        /* prepareStartPlayerJS_onPlayerReady = " . count($prepareStartPlayerJS_onPlayerReady) . ", prepareStartPlayerJS_getDataSetup = " . count($prepareStartPlayerJS_getDataSetup) . " */
        if (typeof player === 'undefined') {
            player = videojs('mainVideo'" . (self::getDataSetup(implode(" ", $prepareStartPlayerJS_getDataSetup))) . ");
            ";
        if (!empty($IMAADTag) && !isLive()) {
            $js .= "var options = {id: 'mainVideo', adTagUrl: '{$IMAADTag}'}; player.ima(options);";
            $js .= "setInterval(function(){ fixAdSize(); }, 300);
                // first time it's clicked.
                var startEvent = 'click';";
            if (isMobile()) {
                $js .= "// Remove controls from the player on iPad to stop native controls from stealing
                // our click
                var contentPlayer = document.getElementById('content_video_html5_api');
                if (contentPlayer && (navigator.userAgent.match(/iPad/i) ||
                        navigator.userAgent.match(/Android/i)) &&
                        contentPlayer.hasAttribute('controls')) {
                    contentPlayer.removeAttribute('controls');
                }

                // Initialize the ad container when the video player is clicked, but only the
                if (navigator.userAgent.match(/iPhone/i) ||
                        navigator.userAgent.match(/iPad/i) ||
                        navigator.userAgent.match(/Android/i)) {
                    startEvent = 'touchend';
                }";
            }

            $js .= "player.one(startEvent, function () {player.ima.initializeAdDisplayContainer();});";
        }

        $js .= "}
        player.ready(function () {";
        $js .= "var err = this.error();
            if (err && err.code) {
                $('.vjs-error-display').hide();
                $('#mainVideo').find('.vjs-poster').css({'background-image': 'url({$global['webSiteRootURL']}plugin/Live/view/Offline.jpg)'});
            }
            " . implode(PHP_EOL, $prepareStartPlayerJS_onPlayerReady) . "
            playerPlayIfAutoPlay({$currentTime});
        });
        player.persistvolume({
            namespace: 'AVideo'
        });";

        if ($obj->showLoopButton && !isLive()) {
            $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/loopbutton.js');
        }


        $js .= file_get_contents($global['systemRootPath'] . 'plugin/PlayerSkins/fixCurrentSources.js');
        if (empty($noReadyFunction)) {
            $js .= "});";
        }
        $getStartPlayerJSWasRequested = true;
        return $js;
    }

    static function setIMAADTag($tag) {
        global $IMAADTag;
        $IMAADTag = $tag;
    }

    static function playerJSCodeOnLoad($videos_id, $nextURL = "") {
        $js = "";
        $videos_id = intval($videos_id);
        if (empty($videos_id)) {
            return false;
        }
        $video = new Video("", "", $videos_id);
        if (empty($nextURL)) {
            $next_video = Video::getVideo($video->getNext_videos_id());
            if (!empty($next_video['id'])) {
                $nextURL = Video::getURLFriendly($next_video['id'], isEmbed());
            }
        }
        $url = Video::getURLFriendly($videos_id);
        $js .= "
        player.on('play', function () {
            addView({$videos_id}, this.currentTime());
        });
        player.on('timeupdate', function () {
            var time = Math.round(this.currentTime());
            var url = '{$url}';
            if (url.indexOf('?') > -1) {
            url += '&t=' + time;
            } else {
            url += '?t=' + time;
            }
            $('#linkCurrentTime, .linkCurrentTime').val(url);
            if (time >= 5 && time % 5 === 0) {
                addView({$videos_id}, time);
            }
        });
        player.on('ended', function () {
            var time = Math.round(this.currentTime());
            addView({$videos_id}, time);
        });";

        if (!empty($nextURL)) {
            $js .= "playNextURL = '{$nextURL}';";
            $js .= "player.on('ended', function () {setTimeout(function(){playNext(playNextURL);},playerHasAds()?2000:500);});";
        }
        self::getStartPlayerJS($js);
        return true;
    }

    static private function prepareStartPlayerJS($onPlayerReady = "", $getDataSetup = "") {
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

    static function isAutoplayEnabled() {
        global $config;
        if(isLive()){
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

    public static function getVideoTags($videos_id) {
        if (empty($videos_id)) {
            return array();
        }
        $name = "PlayeSkins_getVideoTags{$videos_id}";
        $tags = ObjectYPT::getCache($name, 0);
        if (empty($tags)) {
            //_error_log("Cache not found $name");
            $video = new Video("", "", $videos_id);
            $fileName = $video->getFilename();
            $resolution = Video::getHigestResolution($fileName);
            $obj = new stdClass();
            if (empty($resolution) || empty($resolution['resolution_text'])) {
                $obj->label = '';
                $obj->type = "";
                $obj->text = "";
            } else {
                $obj->label = 'Plugin';
                $obj->type = "danger";
                $obj->text = $resolution['resolution_text'];
            }
            $tags = $obj;
            ObjectYPT::setCache($name, $tags);
        }
        return array($tags);
    }

}
