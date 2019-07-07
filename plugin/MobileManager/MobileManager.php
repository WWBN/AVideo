<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class MobileManager extends PluginAbstract {
    
    public static function getVersion(){
        return 2;
    }
    
    public function getDescription() {
        $desc = "Manage the Mobile App";
        $desc .= $this->isReadyLabel(array('API')); 
        return $desc;
    }

    public function getName() {
        return "MobileManager";
    }

    public function getUUID() {
        return "4c1f4f76-b336-4ddc-a4de-184efe715c09";
    }

    public function getPluginVersion() {
        return "1.0";   
    }

    public function getTags() {
        return array('free', 'mobile', 'android', 'ios');
    }  
        
    public function getEmptyDataObject() {   
        global $global;   
        $obj = new stdClass();                
        //$obj->aboutPage = ""; 
        //$obj->disableGif = false;             
        $obj->doNotAllowAnonimusAccess = false;
        
        $obj->doNotAllowUpload = false;
        
        $obj->hideCreateAccount = false;
        $obj->hideTabTrending = false;
        $obj->hideTabLive = false;
        $obj->hideTabSubscription = false;
        $obj->hideTabPlayLists = false;
        
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "This Software must be used for Good, never Evil. It is expressly forbidden to use this app to build porn sites, violence, racism or anything else that affects human integrity or denigrates the image of anyone.\n"
                . "Any complaints, or through the application or any other electronic means will be analyzed and in case of any criteria established by the developer or local laws, are disrespected, we reserve the right to block and ban any site from our systems\n"
                . "The banned site will be prohibited from using any of our resources, including mobile applications, encoder, plugins, etc.";     
        $obj->EULA = $o;
        
        
        $obj->themeDark = false;
        $obj->portraitImage = false;
        $obj->netflixStyle = false;
        
        //$obj->netflixPlayList = true;
        //$obj->netflixPlayListAutoPlay = true;
        $obj->netflixDateAdded = true;
        //$obj->netflixDateAddedAutoPlay = true;
        $obj->netflixMostPopular = true;
        //$obj->netflixMostPopularAutoPlay = true;
        $obj->netflixMostWatched = true;
        //$obj->netflixMostWatchedAutoPlay = true;
        $obj->netflixCategories = true;
        //$obj->netflixCategoriesAutoPlay = true;
        //$obj->netflixSortByName = false;
        $obj->netflixBigVideo = true;
        
        
        $obj->disableWhitelabel = false;
        
        return $obj;
    }
    
    public function upload(){
    }

}
