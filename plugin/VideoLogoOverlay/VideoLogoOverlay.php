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
        return $obj;
    }

    public function getPluginVersion() {
        return "2.1";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/VideoLogoOverlay/pluginMenu.html';
        return file_get_contents($filename);
    }

    static function getStyle() {
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");
        $opacity = "opacity: " . ($obj->opacity / 100) . "; filter: alpha(opacity={$obj->opacity}); ";

        return $opacity;
    }

    static function getClass() {
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");
        $position = "VideoLogoOverlay";
        switch ($obj->position->value) {
            case "center":
                $position .= " VideoLogoOverlay-Center";
                break;
            case "top":
                $position .= " VideoLogoOverlay-Top";
                break;
            case "bottom":
                $position .= " VideoLogoOverlay-Bottom";
                break;
            case "top left":
                $position .= " VideoLogoOverlay-Top-Left";
                break;
            case "bottom left":
                $position .= " VideoLogoOverlay-Bottom-Left";
                break;
            case "top right":
                $position .= " VideoLogoOverlay-Top-Right";
                break;
            case "bottom right":
                $position .= " VideoLogoOverlay-Bottom-Right";
                break;
        }
        return $position;
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
        global $global;
        if (!isVideo()) {
            return '';
        }
        $style = VideoLogoOverlay::getStyle();
        $url = VideoLogoOverlay::getLink();
        $class = VideoLogoOverlay::getClass();
        $obj = AVideoPlugin::getObjectData("VideoLogoOverlay");
        $logoOverlay = "{$global['webSiteRootURL']}videos/logoOverlay.png";
        //$cols = "col-lg-12 col-md-8 col-sm-7 col-xs-6";
        if ($obj->useUserChannelImageAsLogo) {
            $users_id = 0;
            if ($liveLink_id = isLiveLink()) {
                $liveLink = new LiveLinksTable($liveLink_id);
                $users_id = $liveLink->getUsers_id();
            } else if ($live = isLive()) {
                //$live = array('key' => false, 'live_servers_id' => false, 'live_index' => false);
                $lt = LiveTransmition::getFromKey($live['key']);
                $users_id = $lt['users_id'];
            } else {
                $videos_id = getVideos_id();
                $video = Video::getVideoLight($videos_id);
                $users_id = $video['users_id'];
            }
            if (!empty($users_id)) {
                $logoOverlay = User::getPhoto($users_id);
                $url = User::getChannelLink($users_id);
                $class .= ' VideoLogoOverlay-User';
                //$cols = "col-lg-12 col-md-8 col-sm-7 col-xs-6";
            }
        }
        $cols = "";
        
        if (!empty($url)) {
            $class .= ' VideoLogoOverlay-URL';
        }
        //$logoOverlay = "{$global['webSiteRootURL']}videos/logoOverlay.png";

        $html = '<div style="' . $style . '" class="' . $class . '"><a href="' . $url . '" target="_blank"> <img src="' . $logoOverlay . '" alt="Logo"  class="img img-responsive ' . $cols . '" ></a></div>';
        $js = "$('{$html}').appendTo('#mainVideo');";
        PlayerSkins::addOnPlayerReady($js);
    }

    public function getHeadCode() {
        global $global;
        return "<link href=\"{$global['webSiteRootURL']}plugin/VideoLogoOverlay/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
    }

}
