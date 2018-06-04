<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class NextButton extends PluginAbstract {

    public function getDescription() {
        return "Add next button to the control bar";
    }

    public function getName() {
        return "NextButton";
    }

    public function getUUID() {
        return "5310b394-b54f-48ab-9049-995df4d95239";
    }   
    
    public function getHeadCode() {
        global $global, $autoPlayVideo;
        if (!empty($autoPlayVideo['url'])) {
            $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/NextButton/style.css" rel="stylesheet" type="text/css"/>';
            $css .= '<style></style>';
            return $css;
        }
        
    }    
    public function getFooterCode() {
        global $global, $autoPlayVideo;
        if (!empty($autoPlayVideo['url'])) {
            $tmp = "mainVideo";
            if(($_SESSION['type']=="audio")||($_SESSION['type']=="linkAudio")){
                $tmp = "mainAudio";
            }
            $js = '<script>var autoPlayVideoURL="'.$autoPlayVideo['url'].'"; var videoJsId = "'.$tmp.'";</script>';
            $js .= '<script src="' . $global['webSiteRootURL'] . 'plugin/NextButton/script.js" type="text/javascript"></script>';

            return $js;
        }
    }
        
    public function getTags() {
        return array('free', 'buttons', 'video player');
    }



}
