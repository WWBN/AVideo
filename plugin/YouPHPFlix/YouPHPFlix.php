<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class YouPHPFlix extends PluginAbstract {

    public function getDescription() {
        return "Make the first page looks like a Netflix site<br /><b>LiteGalleryMaxTooltipChars: </b>0 disable the Tooltip";
    }

    public function getName() {
        return "YouPHPFlix";
    }

    public function getUUID() {
        return "e2a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->pageDots = true;
        $obj->LiteDesign = false;
        $obj->LiteGallery = false;
        $obj->LiteGalleryMaxTooltipChars = 250;
        $obj->LiteGalleryNoGifs = false;
        $obj->LiteDesignNoGifs = false;
        $obj->DefaultDesign = true;
        $obj->MostPopular = true;
        $obj->MostWatched = true;
        $obj->DateAdded = true;
        $obj->LiteDesignGenericNrOfRows = 10;
        $obj->SortByName = false;
        $obj->separateAudio = false;
        return $obj;
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
    
    public function getTags() {
        return array('free', 'firstPage', 'netflix');
    }

    
}
