<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class MobileManager extends PluginAbstract {
    
    public static function getVersion(){
        return 1;
    }
    
    public function getDescription() {
        return "Manage the Mobile App";
    }

    public function getName() {
        return "MobileManager";
    }

    public function getUUID() {
        return "4c1f4f76-b336-4ddc-a4de-184efe715c09";
    }

    public function getTags() {
        return array('free', 'mobile', 'android', 'ios');
    }  
        
    public function getEmptyDataObject() {   
        global $global;   
        $obj = new stdClass();                
        $obj->aboutPage = "";              
        $obj->doNotAllowAnonimusAccess = false;
        $obj->disableGif = false;
        return $obj;
    }
    
    public function upload(){
    }

}
