<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class YouPHPFlix extends PluginAbstract {

    public function getDescription() {
        return "Make the first page looks like a Netflix site";
    }

    public function getName() {
        return "YouPHPFlix";
    }

    public function getUUID() {
        return "e2a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }
        
    public function getFirstPage(){
        global $global;        
        return $global['systemRootPath'].'plugin/YouPHPFlix/view/firstPage.php';
    }   
        
    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        $css = "";
        //$css .= "<link href=\"{$global['webSiteRootURL']}view/css/custom/".$obj->theme.".css\" rel=\"stylesheet\" type=\"text/css\"/>";
        $css .= "<link href=\"{$global['webSiteRootURL']}plugin/YouPHPFlix/view/css/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
        
        return $css;
    }

    
}
