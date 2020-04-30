<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class VideoLandscapeFullscreen extends PluginAbstract {

    public function getPluginVersion(){
        return "1.0";
    }

    public function getDescription() {
        return "Activating auto landscape fullscreen in mobile devices";
    }

    public function getName() {
        return "VideoLandscapeFullscreen";
    }

    public function getUUID() {
        return "f1932cc2-0e92-47a5-aa03-08a752777438";
    }

   public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->enterOnRotate = True;
        $obj->alwaysInLandscapeMode = True;
        $obj->iOS = True;
        return $obj;
    }

    public function getFooterCode(){
        global $video;
        if (empty($_GET['videoName']) || (!empty($video['type']) && $video['type']=="embed")) {
            return "";
        }
        global $global;
       $obj3 = AVideoPlugin::getObjectData('VideoLandscapeFullscreen');
       $js = '<script src="'.$global['webSiteRootURL'].'plugin/VideoLandscapeFullscreen/videojs-landscape-fullscreen.js" type="text/javascript"></script>';
        $js .= '<script>'
               . 'if(typeof player == \'undefined\'){player = videojs(\'mainVideo\'' . PlayerSkins::getDataSetup() . ');}player = videojs(\'mainVideo\').landscapeFullscreen({fullscreen: {enterOnRotate: ' . $obj3->enterOnRotate .', alwaysInLandscapeMode: ' . $obj3->alwaysInLandscapeMode .', iOS: ' . $obj3->iOS .'}});'
                . '</script>';
        return $js;
    }


}

