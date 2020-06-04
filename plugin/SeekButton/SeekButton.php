<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class SeekButton extends PluginAbstract {

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
        return "1.0";
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
            $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/SeekButton/videojs-seek-buttons/videojs-seek-buttons.css" rel="stylesheet" type="text/css"/>';
            $css .= '<style>.video-js .vjs-seek-button {font-size: 25px;width: 2em !important;}</style>';
        }
        return $css;
    }

    public function getFooterCode() {
        global $global;
        if (isVideoPlayerHasProgressBar()) {
            $obj = $this->getDataObject();
            $js = '<script src="' . $global['webSiteRootURL'] . 'plugin/SeekButton/videojs-seek-buttons/videojs-seek-buttons.min.js" type="text/javascript"></script>';
            if (!empty($_SESSION['type'])) {
                if (($_SESSION['type'] == "audio") || ($_SESSION['type'] == "linkAudio")) {
                    $js .= '<script>$(document).ready(function () {  setTimeout(function(){ if(typeof player == \'undefined\'){player = videojs(\'mainAudio\''.PlayerSkins::getDataSetup().');} ';
                } else {
                    $js .= '<script>$(document).ready(function () {  setTimeout(function(){ if(typeof player == \'undefined\'){player = videojs(\'mainVideo\''.PlayerSkins::getDataSetup().');} ';
                }
            } else {
                $js .= '<script>$(document).ready(function () {  setTimeout(function(){ if(typeof player == \'undefined\'){player = videojs(\'mainVideo\''.PlayerSkins::getDataSetup().');} ';
            }
            $js .= 'player.seekButtons({forward: ' . $obj->forward . ',back: ' . $obj->back . ' }); }, 30); });' . '</script>';
            return $js;
        }
    }

    public function getTags() {
        return array('free', 'buttons', 'video player');
    }

}
