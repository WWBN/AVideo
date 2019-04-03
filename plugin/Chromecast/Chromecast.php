<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Chromecast extends PluginAbstract {

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
        if (!empty($_GET['videoName'])) {
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/Chromecast/videojs-chromecast/silvermine-videojs-chromecast.css" rel="stylesheet" type="text/css"/>';
        }
        return $css;
    }

    public function getFooterCode() {
        global $global;
        if (!empty($_GET['videoName'])) {
            include $global['systemRootPath'] . 'plugin/Chromecast/footer.php';
        }
    }

}
