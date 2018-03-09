<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Gallery extends PluginAbstract {

    public function getDescription() {
        return "Make the first page works as a gallery";
    }

    public function getName() {
        return "Gallery";
    }

    public function getUUID() {
        return "a06505bf-3570-4b1f-977a-fd0e5cab205d";
    }
        
    public function getHeadCode() {
        global $global;
        // preload image
        $js = "<script>var img1 = new Image();img1.src=\"{$global['webSiteRootURL']}view/img/video-placeholder.png\";</script>";
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>';
        return $js.$css;
    }
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->Description = false;
        return $obj;
    }
    
    public function getFirstPage(){
        global $global;
        return $global['systemRootPath'].'plugin/Gallery/view/modeGallery.php';
    }   
    
    public function getTags() {
        return array('free', 'firstPage', 'gallery');
    }
    
}
