<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class FBTube extends PluginAbstract {
    
    public function getDescription() {
        return "Make the first page works as a facebook page";
    }

    public function getName() {
        return "Facebook Tube";
    }

    public function getUUID() {
        return "214d4c2f-1471-4592-81de-095e68ad14ea";
    }
        
    public function getFirstPage(){
        global $global;
        return $global['systemRootPath'].'plugin/FBTube/view/modeFacebook.php';
    }

    
    public function getHeadCode(){
        global $global;
        return '<link href="'.$global['webSiteRootURL'].'plugin/FBTube/view/style.css" rel="stylesheet" type="text/css"/>';
    }
    
    public function getTags() {
        return array('free', 'firstPage', 'facebook');
    }

}