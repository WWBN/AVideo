<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Hotkeys extends PluginAbstract {

    public function getDescription() {
        global $global;
        return "Enable hotkeys for videos, like F for fullscreen, space for play/pause, etc..<br />Author: <a href='http://hersche.github.io' target='_blank' >Vinzenz Hersche</a>";
    }

    public function getName() {
        return "Hotkeys";
    }

    public function getUUID() {
        return "11355314-1b30-ff15-afb-67516fcccff7";
    }
    
    public function getHelp(){
        $obj = $this->getDataObject();
        $html = "<h2 id='Hotkeys help' >Hotkeys</h2><p>".__("When you are watching media, you can use these keyboard-shortcuts.")."</p><table class='table'><tbody>";
        $html .= "<tr><td>".__("Seek")."</td><td>".__("Left")."/".__("right")."-".__("arrow")."</td></tr><tr><td>";
        if($obj->ReplaceVolumeWithPlusMinus){
            $html .= __("Volume")."</td><td>+/-</td></tr>";
        } else {
            $html .= __("Volume")."Volume</td><td>".__("Up")."/".__("Down")."-".__("Arrow")."</td></tr>";
        }
        if($obj->Fullscreen){
            $html .= "<tr><td>".__("Fullscreen")."</td><td>".$obj->FullscreenKey."</td></tr>";
        } 
        if($obj->PlayPauseKey==" "){
            $html .= "<tr><td>".__("Play")."/".__("pause")."</td><td>".__("space")."</td></tr>";
        } else {
           $html .= "<tr><td>".__("Play")."/".__("pause")."</td><td>".$obj->PlayPauseKey."</td></tr>"; 
        }    
        return $html."</tbody></table>";
    }
    public function getJSFiles(){
        if(!empty($_GET['isMediaPlaySite'])){
            return array("plugin/Hotkeys/videojs.hotkeys.min.js");
        }
        return array();
    }
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->Volume = true;
        $obj->ReplaceVolumeWithPlusMinus = true;
        $obj->Fullscreen = true;
        $obj->FullscreenKey = "F";
        $obj->PlayPauseKey = " ";
        $obj->AlwaysCaptureHotkeys = true;
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'videos', 'hotkeys');
    }
    

    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();

        if(!empty($_GET['isMediaPlaySite'])){
            $tmp = "<script> $( document ).ready(function() {";
            if($_SESSION['type']=="audio"){
                $tmp .= "videojs('mainAudio').ready(function() {";
            } else {
                $tmp .= "videojs('mainVideo').ready(function() {";
            }
            $tmp .= "this.hotkeys({ seekStep: 5,";
               
            if($obj->Volume){
                $tmp .= "enableVolumeScroll: true,";
            } else {
                // Could not use Up/Down-Keys as excepted. What's the right option?
                $tmp .= "enableVolumeScroll: false,";
            }
            if($obj->AlwaysCaptureHotkeys){
                $tmp .= "alwaysCaptureHotkeys: true,";
            } else {
                $tmp .= "alwaysCaptureHotkeys: false,";
            }     
            if($obj->Fullscreen){
                $tmp .= "enableFullscreen: true,";
            } else {
                $tmp .= "enableFullscreen: false,";
            }
            if(($obj->FullscreenKey!=="F")||($obj->FullscreenKey!=="")){
                $tmp .= "fullscreenKey: function(event, player) { return (event.which ===".ord($obj->FullscreenKey)."); },";
            }
            if(($obj->PlayPauseKey!==" ")||($obj->PlayPauseKey!=="")){
                $tmp .= "playPauseKey: function(event, player) { return (event.which ===".ord($obj->PlayPauseKey)."); },";
            }
            if($obj->ReplaceVolumeWithPlusMinus){
                $tmp .= "volumeUpKey: function(event, player) { return (event.which === 107); },
                         volumeDownKey: function(event, player) { return (event.which === 109);},";
            }
            
            $tmp .= "enableModifiersForNumbers: false
                      });  
            });";

            $tmp .= "});</script>";
            return $tmp;
        }
        return "";
    }
    
}
