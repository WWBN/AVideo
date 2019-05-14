<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class VideoLogoOverlay extends PluginAbstract {

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
        $obj->position = "";
        $obj->opacity = 50;
        $obj->position_options = array('center', 'top', 'bottom', 'top left', 'bottom left', 'top right', 'bottom right');
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/VideoLogoOverlay/pluginMenu.html';
        return file_get_contents($filename);
    }

    static function getStyle() {
        $obj = YouPHPTubePlugin::getObjectData("VideoLogoOverlay");

        "position: absolute; top: 0; left: 0; opacity: 0.5; filter: alpha(opacity=50);";
        $opacity = "opacity: " . ($obj->opacity / 100) . "; filter: alpha(opacity={$obj->opacity});pointer-events:none; ";

        $position = "position: absolute; top: 0; left: 0; ";
        switch ($obj->position) {
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
        return $position.$opacity;
    }
    
    static function getLink() {
        $obj = YouPHPTubePlugin::getObjectData("VideoLogoOverlay");
        if(!empty($obj->url)){
            $url = $obj->url;
        }else{
            $url = "#";
        }
        return $url;
    }
    
    
    public function getTags() {
        return array('free');
    }

}
