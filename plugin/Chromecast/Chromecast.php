<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Chromecast extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        return "A plugin that adds a button to the control bar which will cast videos to a Chromecast.";
    }

    public function getName() {
        return "Chromecast";
    }

    public function getUUID() {
        return "cast1de9-b4f7-4462-bda6-458b0736593d";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        return $obj;
    }

    public function getHeadCode() {
        global $global;
        $css = "";
        if (isVideo()) {
            $css .= '<link href="' .getCDN() . 'plugin/Chromecast/videojs-chromecast/silvermine-videojs-chromecast.css" rel="stylesheet" type="text/css"/>';
            $css .= "<style>.vjs-chromecast-button .vjs-icon-placeholder {width: 20px;height: 20px;</style>";
        }
        return $css;
    }

    public function getFooterCode() {
        global $global;
        if (isVideoOrAudioNotEmbed()) {
            include $global['systemRootPath'] . 'plugin/Chromecast/footer.php';
        }
    }

}
