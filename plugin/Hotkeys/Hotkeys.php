<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Hotkeys extends PluginAbstract {

    public function getDescription() {
        global $global;
        return "Enable hotkeys for videos (experimental)";
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
        $obj->Fullscreen = True;
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'videos', 'hotkeys');
    }
    

    public function getFooterCode() {
        global $global;
        $obj = $this->getDataObject();
        if(("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL'])&&("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL'])&&("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."cat/")&&("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."cat/")&&("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."login/")&&("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."login/")&&("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."mvideos")&&("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']!=$global['webSiteRootURL']."mvideos")){
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
        
        $tmp .= "enableModifiersForNumbers: false
                      });  
                   });  </script>";
                   return $tmp;
        }
        return "";
    }
    
}
