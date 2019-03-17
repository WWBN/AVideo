<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class VR360 extends PluginAbstract {

    private $script = 'panorama';

    public function getDescription() {
        return "Panoramic 360 video player. Project video onto different shapes";
    }

    public function getName() {
        return "VR360";
    }

    public function getUUID() {
        return "ce09988f-e578-4b91-96bb-4b701e5c0884";
    }

    public function getHTMLMenuRight() {
        global $global;
        if (empty($_GET['videoName']) || !User::canUpload()) {
            return "";
        }
        $video = Video::getVideoFromCleanTitle($_GET['videoName']);
        $videos_id = $video['id'];
        $is_enabled = VideosVR360::isVR360Enabled($videos_id);
        include $global['systemRootPath'] . 'plugin/VR360/view/menuRight.php';
    }

    public function getHeadCode() {
        return $this->getHeadPanorama();
    }

    public function getFooterCode() {
        return $this->getFooterPanorama();
    }

    private function getHeadPanorama() {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        if (empty($_GET['videoName']) || !VideosVR360::isVR360EnabledByVideoCleanTitle($_GET['videoName'])) {
            return "";
        }
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/VR360/videojs-panorama/videojs-panorama.css" rel="stylesheet" type="text/css"/>';
        $css .= '<style></style>';
        return $css;
    }

    private function getFooterPanorama() {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        if (empty($_GET['videoName']) || !VideosVR360::isVR360EnabledByVideoCleanTitle($_GET['videoName'])) {
            return "";
        }
        $js = '<script src="' . $global['webSiteRootURL'] . 'plugin/VR360/videojs-panorama/videojs-panorama.v5.js" type="text/javascript"></script>';
        $js .= '<script src="' . $global['webSiteRootURL'] . 'plugin/VR360/videojs-panorama/three.min.js" type="text/javascript"></script>';
        $js .= '<script>
    (function(window, videojs) {
        var player;
        if (typeof player === \'undefined\') {

        player = window.player = videojs(\'mainVideo\', {}, function () {
            window.addEventListener("resize", function () {
                var canvas = player.getChild(\'Canvas\');
                if(canvas) canvas.handleResize();
            });
        });
        }
        var videoElement = document.getElementById("mainVideo");
        var width = videoElement.offsetWidth;
        var height = videoElement.offsetHeight;
        player.width(width), player.height(height);
        player.panorama({
            clickToToggle: (!isMobile()),
            autoMobileOrientation: true,
            initFov: 100,
            VREnable: isMobile(),
            NoticeMessage: (isMobile())? "please drag and drop the video" : "please use your mouse drag and drop the video",
            callback: function () {
                if(!isMobile()) player.play();
            }
        });

        player.on("VRModeOn", function(){
            if(!player.isFullscreen())
                player.controlBar.fullscreenToggle.trigger("tap");
        });
    }(window, window.videojs));</script>';
        return $js;
    }
    
    public function getTags() {
        return array('free');
    }

}
