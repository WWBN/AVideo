<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class TheaterButton extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription() {
        return "Add next theater switch button to the control bar";
    }

    public function getName() {
        return "TheaterButton";
    }

    public function getUUID() {
        return "f7596843-51b1-47a0-8bb1-b4ad91f87d6b";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->show_switch_button = true;
        $obj->compress_is_default = false;
        return $obj;
    }

    public function getHeadCode() {
        global $global;
        if (!$this->showButton()) {
            return "";
        }
        $tmp = "mainVideo";
        if (!empty($_SESSION['type'])) {
            if (($_SESSION['type'] == "audio") || ($_SESSION['type'] == "linkAudio")) {
                $tmp = "mainVideo";
            }
        }
        $css = '<link href="' .getCDN() . 'plugin/TheaterButton/style.css?' . filemtime($global['systemRootPath'] . 'plugin/TheaterButton/style.css') . '" rel="stylesheet" type="text/css"/>';
        $css .= '<script>var videoJsId = "' . $tmp . '";</script>';
        $css .= '<script>var isCompressed = ' . (self::isCompressed()?"true":"false") . ';</script>';
        return $css;
    }

    public function getJSFiles() {
        global $global, $autoPlayVideo, $isEmbed;
        if (!$this->showButton()) {
            return "";
        }
        $obj = $this->getDataObject();
        
        if (!empty($obj->show_switch_button)) {
            return array("plugin/TheaterButton/script.js", "plugin/TheaterButton/addButton.js");
        }
        return array("plugin/TheaterButton/script.js");
    }

    public function getFooterCode() {
        global $global, $autoPlayVideo, $isEmbed;
        if (!$this->showButton()) {
            return "";
        }
        $obj = $this->getDataObject();
        $js = '';
        
        PlayerSkins::getStartPlayerJS("if (player.getChild('controlBar').getChild('PictureInPictureToggle')) {
    player.getChild('controlBar').addChild('Theater', {}, getPlayerButtonIndex('PictureInPictureToggle') + 1);
} else {
    player.getChild('controlBar').addChild('Theater', {}, getPlayerButtonIndex('fullscreenToggle') - 1);
}");
        return $js;
    }

    private function showButton() {
        if (isMobile() || isEmbed()) {
            return false;
        }
        if (isVideo() || isLive() || isAudio()) {
            return true;
        }
        return false;
    }

    static function isCompressed() {
        if (!isset($_COOKIE['compress'])) {
            $obj = AVideoPlugin::getDataObject('TheaterButton');
            return $obj->compress_is_default ? true : false;
        }
        return (!empty($_COOKIE['compress']) && $_COOKIE['compress'] !== 'false') ? true : false;
    }

}
