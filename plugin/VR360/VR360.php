<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';

class VR360 extends PluginAbstract
{

    private $script = 'panorama';

    public function getTags()
    {
        return array(
            PluginTags::$FREE,
        );
    }
    public function getDescription()
    {
        return "Panoramic 360 video player. Project video onto different shapes";
    }

    public function getName()
    {
        return "VR360";
    }

    public function getUUID()
    {
        return "ce09988f-e578-4b91-96bb-4b701e5c0884";
    }

    public function getHTMLMenuRight()
    {
        global $global;
        $videos_id = getVideos_id();
        Video::canEdit($videos_id);
        if (Video::canEdit($videos_id)) {
            $is_enabled = VideosVR360::isVR360Enabled($videos_id);
            include $global['systemRootPath'] . 'plugin/VR360/view/menuRight.php';
        }
    }

    public function getHeadCode()
    {
        return $this->getHeadPanorama();
    }

    public function getFooterCode()
    {
        return $this->getFooterPanorama();
    }

    private function getHeadPanorama()
    {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        $is_enabled = VideosVR360::isVR360Enabled(getVideos_id());
        if (!$is_enabled) {
            return '<!-- videojs-vr is disabled -->';
        }
        $css = '';
        $css .= '<link href="' . getURL('node_modules/videojs-vr/dist/videojs-vr.css') . '" rel="stylesheet" type="text/css"/>';
        $css .= '<style>#mainVideo > canvas {left: 0;} #mainVideo > div.vjs-text-track-display{pointer-events:none !important;}</style>';
        return $css;
    }

    private function getFooterPanorama()
    {
        global $global;
        require_once $global['systemRootPath'] . 'plugin/VR360/Objects/VideosVR360.php';
        $is_enabled = VideosVR360::isVR360Enabled(getVideos_id());
        if (!$is_enabled) {
            return '<!-- videojs-vr is disabled -->';
        }
        /**/
        $js = '<script src="' . getURL('node_modules/videojs-vr/dist/videojs-vr.min.js') . '" type="text/javascript"></script>';
        $onPlayerReady = " 
        player.mediainfo = player.mediainfo || {};
        player.mediainfo.projection = '360';
        var vr = window.vr = player.vr({projection: 'AUTO', motionControls: isMobile()});";
        $js .= '<script>' . PlayerSkins::getStartPlayerJS($onPlayerReady) . '</script>';
        
        return $js;
    }
}
