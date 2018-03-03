<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Hotkeys extends PluginAbstract {

    public function getDescription() {
        global $global;
        return "Enable hotkeys for videos, like F for fullscreen, space for play/pause, etc..";
    }

    public function getName() {
        return "Hotkeys";
    }

    public function getUUID() {
        return "11355314-1b30-ff15-afb-67516fcccff7";
    }
        
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->Volume = True;
        $obj->ReplaceVolumeWithPlusMinus = True;
        $obj->Fullscreen = True;
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'videos', 'hotkeys');
    }
    

    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();
        
        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(("https://".$url!=$global['webSiteRootURL'])&&("http://".$url!=$global['webSiteRootURL'])&&
           ("http://".$url!=$global['webSiteRootURL']."cat/")&&("https://".$url!=$global['webSiteRootURL']."cat/")&&
           ("http://".$url!=$global['webSiteRootURL']."login/")&&("http://".$url!=$global['webSiteRootURL']."login/")&&
           ("http://".$url!=$global['webSiteRootURL']."mvideos")&&("https://".$url!=$global['webSiteRootURL']."mvideos")&&
           ("http://".$url!=$global['webSiteRootURL']."plugins")&&("https://".$url!=$global['webSiteRootURL']."plugins")&&(strpos($url,"/cat/")===false)){
            
            $tmp = "<script src=\"{$global['webSiteRootURL']}plugin/Hotkeys/videojs.hotkeys.min.js\"> </script>
                    <script>
                        videojs('mainVideo').ready(function() {
                            this.hotkeys({
                            seekStep: 5,";
               
            if($obj->Volume==1){
                $tmp .= "enableVolumeScroll: true,";
            } else {
                // Could not use Up/Down-Keys as excepted. What's the right option?
                $tmp .= "enableVolumeScroll: false,";
            }
               
            if($obj->Fullscreen==1){
                $tmp .= "enableFullscreen: true,";
            } else {
                $tmp .= "enableFullscreen: false,";
            }
            if($obj->ReplaceVolumeWithPlusMinus==1){
                $tmp .= "volumeUpKey: function(event, player) { return (event.which === 107); },
                         volumeDownKey: function(event, player) { return (event.which === 109);},";
            }
            
            $tmp .= "enableModifiersForNumbers: false
                      });  
            });";

            $tmp .= "</script>";
            return $tmp;
        }
        return "";
    }
    
}
