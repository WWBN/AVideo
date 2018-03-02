<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class YouPHPFlixHybrid extends PluginAbstract {

    public function getDescription() {
        return "This is a merge of YouPHPFlix-Plugin and the Gallery-Plugin, which are also dependencies.";
    }

    public function getName() {
        return "YouPHPFlixHybrid";
    }

    public function getUUID() {
        return "d3sa2k4l3-23rds421-re323-4ae-423";
    }
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->pageDots = true;
        return $obj;
    }
        
    public function getFirstPage(){
        global $global;        
        if("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL']){
            return $global['systemRootPath'].'plugin/YouPHPFlix/view/firstPage.php';
        }
        else {
            return $global['systemRootPath'].'plugin/Gallery/view/modeGallery.php';
        }
    }   
        
    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        $css = "";
        //$css .= "<link href=\"{$global['webSiteRootURL']}view/css/custom/".$obj->theme.".css\" rel=\"stylesheet\" type=\"text/css\"/>";
        if("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL']){
            $css .= "<link href=\"{$global['webSiteRootURL']}plugin/YouPHPFlix/view/css/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
        }
        return $css;
    }
    
    public function getTags() {
        return array('free', 'firstPage', 'netflix', 'gallery', 'fork');
    }

    
}
