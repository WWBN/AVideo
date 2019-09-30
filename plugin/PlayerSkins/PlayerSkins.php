<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

class PlayerSkins extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Customize your playes Skin<br>The Skis options are: ";
        $dir = $global['systemRootPath'] . 'plugin/PlayerSkins/skins/';
        $names = array();
        foreach (glob($dir . '*.css') as $file) {
            $path_parts = pathinfo($file);
            $names[] = $path_parts['filename'];
        }
        return $desc . "<code>" . implode($names, "</code> or <code>") . "</code>";
    }

    public function getName() {
        return "PlayerSkins";
    }

    public function getUUID() {
        return "e9a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->skin = "youtube";
        $obj->showPlaybackRates = true;
        $obj->playbackRates = "[0.5, 1, 1.5, 2, 3]";
        return $obj;
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        $css = "";
        if (!empty($_GET['videoName']) || !empty($_GET['u'])) {
            $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/skins/{$obj->skin}.css\" rel=\"stylesheet\" type=\"text/css\"/>";
        }
        return $css;
    }

    public function getFooterCode() {
        global $global;
        if (!empty($_GET['videoName'])) {
            $js = '';

            $js .= '<script>
            $( document ).ready(function() {
            if(typeof player == \'undefined\'){player = videojs(\'mainVideo\');}'
                    . 'videojs(\'mainVideo\', {
  playbackRates: [0.5, 1, 1.5, 2]
});'
                    . '});</script>';
            return $js;
        } else if (basename($_SERVER["SCRIPT_FILENAME"]) === 'managerVideos.php') {
            include $global['systemRootPath'] . 'plugin/SubtitleSwitcher/footer.php';
        }
    }

    public function getTags() {
        return array('free');
    }

}
