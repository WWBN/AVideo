<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
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
        $obj->SubCategorys = false;
        $obj->ForceCategoryLinks = false;
        return $obj;
    }
    
    public function getHelp(){
        if(User::isAdmin()){
            return "<h2 id='YouPHPFlix help'>YouPHPFlix options (admin)</h2><table class='table'><thead><th>Option-name</th><th>Default</th><th>Description</th></thead><tbody><tr><td>DefaultDesign</td><td>checked</td><td>The original style, for each category, one row with the newest videos</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>checked,checked,checked,unchecked</td><td>Metacategories</td></tr><tr><td>LiteDesign</td><td>unchecked</td> <td>All categories in one row</td></tr><tr><td>separateAudio</td><td>unchecked</td><td>Create a own row for audio</td></tr></tbody></table>";   
        }
        return "";
    }
    
    public function getFirstPage(){
        global $global; 
        if(!YouPHPTubePlugin::isEnabled("d3sa2k4l3-23rds421-re323-4ae-423")){
            return $global['systemRootPath'].'plugin/YouPHPFlix/view/firstPage.php';
        }
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
