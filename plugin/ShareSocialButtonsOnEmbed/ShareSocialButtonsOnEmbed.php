<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ShareSocialButtonsOnEmbed extends PluginAbstract {

    public function getDescription() {
        return "Enable Or disable Share Social Buttons on Embed videos";
    }

    public function getName() {
        return "ShareSocialButtonsOnEmbed";
    }

    public function getUUID() {
        return "c63ae9e3-bf01-4f76-9d7e-b3ad7fd92e3e";
    }
    
    public function getFooterCode() {
        global $global, $video, $isEmbed;
        if(empty($isEmbed)){
            return "";
        }
        include 'script.php';
    }
    
    
    public function getTags() {
        return array('free');
    }
}
