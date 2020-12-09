<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class SeekButton extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }
    public function getDescription() {
        return "Add seek buttons to the control bar";
    }

    public function getName() {
        return "SeekButton";
    }

    public function getUUID() {
        return "f5c30980-9530-4650-8eab-9ab461ea6fdb";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->forward = 30;
        $obj->back = 10;
        return $obj;
    }

    public function getHeadCode() {
        global $global;
        $css = "";
        if (isVideoPlayerHasProgressBar()) {
            $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/SeekButton/videojs-seek-buttons/videojs-seek-buttons.css?'. filectime($global['systemRootPath'] . 'plugin/SeekButton/videojs-seek-buttons/videojs-seek-buttons.css').'" rel="stylesheet" type="text/css"/>';
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/SeekButton/seek.css?'. filectime($global['systemRootPath'] . 'plugin/SeekButton/seek.css').'" rel="stylesheet" type="text/css"/>';
            $css .= '<style>.video-js .vjs-seek-button {font-size: 25px;width: 2em !important;}</style>';
            if(isMobile()){
                $css .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">';
            }
        }
        return $css;
    }

    public function getFooterCode() {
        global $global;
        if (isVideoPlayerHasProgressBar()) {
            $obj = $this->getDataObject();
            $js = "";
            if (isVideoPlayerHasProgressBar()) {
                $js .= '<script src="' . $global['webSiteRootURL'] . 'plugin/SeekButton/videojs-seek-buttons/videojs-seek-buttons.min.js" type="text/javascript"></script>';
                $js .= '<script>'
                        . 'var playerSeekForward = ' . $obj->forward . '; '
                        . 'var playerSeekBack = ' . $obj->back . ';'
                        . 'var forwardLayer = ' . json_encode(file_get_contents($global['systemRootPath']."plugin/SeekButton/forward.html")) . ';'
                        . 'var backLayer = ' . json_encode(file_get_contents($global['systemRootPath']."plugin/SeekButton/back.html")) . ';'
                        . '</script>';
                $js .= '<script>'.PlayerSkins::getStartPlayerJS(file_get_contents($global['systemRootPath']."plugin/SeekButton/seek.js")).'</script>';
            }
            return $js;
        }
    }
}
