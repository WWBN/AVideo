<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class VideoLogoOverlay extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }

    public function getDescription() {
        return "Put an Logo overlay on video";
    }

    public function getName() {
        return "VideoLogoOverlay";
    }

    public function getUUID() {
        return "0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->url = "";
        $o = new stdClass();
        $o->type = array('center' => 'Center', 'top' => 'Top', 'bottom' => 'Bottom', 'top left' => 'Top Left', 'bottom left' => 'Bottom Left', 'top right' => 'Top Right', 'bottom right' => 'Bottom Right');
        $o->value = 'top right';
        $obj->position = $o;
        $obj->opacity = 50;
        $obj->useUserChannelImageAsLogo = true;
        $obj->position_options = $o->type;
        return $obj;
    }
    
    public function getPluginVersion() {
        return "2.0";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/VideoLogoOverlay/pluginMenu.html';
        return file_get_contents($filename);
    }

    static function getStyle() {
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");

        "position: absolute; top: 0; left: 0; opacity: 0.5; filter: alpha(opacity=50);";
        $opacity = "opacity: " . ($obj->opacity / 100) . "; filter: alpha(opacity={$obj->opacity});pointer-events:none; ";

        $position = "position: absolute; top: 0; left: 0; ";
        switch ($obj->position->value) {
            case "center":
                $position = "position: absolute; top: 50%; left: 50%; margin-left: -125px; margin-top: -35px; ";
                break;
            case "top":
                $position = "position: absolute; top: 0; left: 50%; margin-left: -125px; ";
                break;
            case "bottom":
                $position = "position: absolute; bottom: 0; left: 50%; margin-left: -125px; ";
                break;
            case "top left":
                $position = "position: absolute; top: 0; left: 0; ";
                break;
            case "bottom left":
                $position = "position: absolute; bottom: 0; left: 0; ";
                break;
            case "top right":
                $position = "position: absolute; top: 0; right: 0; ";
                break;
            case "bottom right":
                $position = "position: absolute; bottom: 0; right: 0; ";
                break;
        }
        return $position . $opacity;
    }

    static function getLink() {
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");

        if (!empty($obj->url)) {
            $url = $obj->url;
        } else {
            $url = "#";
        }
        return $url;
    }

    function getFooterCode() {
        if(!isVideo()){
            return '';
        }
        $videos_id = getVideos_id();
        $video = Video::getVideoLight($videos_id);
        $style = VideoLogoOverlay::getStyle();
        $url = VideoLogoOverlay::getLink();
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");
        $logoOverlay = "{$global['webSiteRootURL']}videos/logoOverlay.png";
        if ($obj->useUserChannelImageAsLogo) {
            $logoOverlay = User::getPhoto($video['users_id']);
        }
        $html = '<div style="' . $style . '" class="VideoLogoOverlay"><a href="' . $url . '" target="_blank"> <img src="' . $logoOverlay . '" alt="Logo"  class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a></div>';
        $js = "$('{$html}').appendTo('#mainVideo');";
        PlayerSkins::addOnPlayerReady($js);
    }

}
