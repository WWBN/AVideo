<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class VideoLandscapeFullscreen extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
        );
    }
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
        $obj->enterOnRotate = true;
        self::addDataObjectHelper('enterOnRotate', 'Enter on Rotate', "Enter fullscreen mode on rotating the device in landscape");
        $obj->alwaysInLandscapeMode = true;
        self::addDataObjectHelper('alwaysInLandscapeMode', 'Always in Landscape Mode', "Always enter fullscreen in landscape mode even when device is in portrait mode (works on chromium, firefox, and ie >= 11");
        $obj->iOS = false;
        self::addDataObjectHelper('iOS', 'iOS', "Whether to use fake fullscreen on iOS (needed for displaying player controls instead of system controls)");
        return $obj;
    }

    public function getFooterCode(){
        global $video;
        if(!isMobile()){
            return "";
        }
        if (!isAVideoPlayer() || (!empty($video['type']) && $video['type']=="embed")) {
            return "";
        }
        global $global;
       $obj3 = AVideoPlugin::getObjectData('VideoLandscapeFullscreen');
       $js = '<script src="'.getURL('node_modules/videojs-landscape-fullscreen/dist/videojs-landscape-fullscreen.min.js').'" type="text/javascript"></script>';
       //$js .= 'if(typeof player == \'undefined\'){player = videojs(\'mainVideo\'' . PlayerSkins::getDataSetup() . ');}player = videojs(\'mainVideo\').landscapeFullscreen({fullscreen: {enterOnRotate: ' . ($obj3->enterOnRotate?"true":"false") .', alwaysInLandscapeMode: ' . ($obj3->alwaysInLandscapeMode?"true":"false") .', iOS: ' . ($obj3->iOS?"true":"false") .'}});';
        $onPlayerReady = 'player.landscapeFullscreen({
            fullscreen: {
              enterOnRotate: ' . ($obj3->enterOnRotate?"true":"false") .',
              exitOnRotate: ' . ($obj3->enterOnRotate?"true":"false") .',
              alwaysInLandscapeMode: ' . ($obj3->alwaysInLandscapeMode?"true":"false") .',
              iOS: ' . ($obj3->iOS?"true":"false") .'
            }
          });';
        PlayerSkins::addOnPlayerReady($onPlayerReady);
        return $js;
    }


}

