<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
class LifeStreamLayout extends PluginAbstract {

    public function getDescription() {
        return "Make the first page works as a LifeStreamLayout";
    }

    public function getName() {
        return "LifeStreamLayout";
    }

    public function getUUID() {
        return "a06522bf-3570-4b1f-977a-fd0e5cab205d";
    }

    public function getPluginVersion() {
        return "1.0";   
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        // preload image
        $js = "<script>var img1 = new Image();img1.src=\"{$global['webSiteRootURL']}view/img/video-placeholder.png\";</script>";
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/LifeStreamLayout/style.css" rel="stylesheet" type="text/css"/>';
        if(!empty($obj->playVideoOnFullscreen) && !empty($_GET['videoName'])){
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/LifeStreamLayout/fullscreen.css" rel="stylesheet" type="text/css"/>';
        }
        if(!empty($obj->playVideoOnFullscreen)){
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        
        return $js.$css;
    }
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "<img src=\"https://servedbyadbutler.com/getad.img?libBID=570126&dl=1\" class=\"img img-responsive\" style='width:100%'/>";        
        $obj->topAd = $o;
        $obj->topAdCarousel = false;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "<img src='{$global['webSiteRootURL']}plugin/LifeStreamLayout/img/adLeftOne.jpg' class='img img-responsive'/><img src='{$global['webSiteRootURL']}plugin/LifeStreamLayout/img/adLeftTwo.jpg' class='img img-responsive'/>";        
        $obj->leftAd = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "<img src='{$global['webSiteRootURL']}plugin/LifeStreamLayout/img/adRightOne.jpg' class='img img-responsive'/><img src='{$global['webSiteRootURL']}plugin/LifeStreamLayout/img/adRightTwo.jpg' class='img img-responsive'/>";        
        $obj->rightAd = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "<img src='{$global['webSiteRootURL']}plugin/LifeStreamLayout/img/centerCode.jpg' class='img img-responsive'/>";        
        $obj->centerCode = $o;
        
        $obj->BigVideo = true;
        $obj->Description = false;
        $obj->CategoryDescription = false;
        $obj->DateAdded = true;
        $obj->DateAddedCustomTitle = "";
        $obj->DateAddedRowCount = 12;
        $obj->MostWatched = true;
        $obj->MostWatchedCustomTitle = "";
        $obj->MostWatchedRowCount = 12;
        $obj->MostPopular = true;
        $obj->MostPopularCustomTitle = "";
        $obj->MostPopularRowCount = 12;
        $obj->SortByName = false;
        $obj->SortByNameCustomTitle = "";
        $obj->SortByNameRowCount = 12;
        $obj->SubscribedChannels = true;
        $obj->SubscribedChannelsRowCount = 12;
        $obj->sortReverseable = false;
        $obj->SubCategorys = false;
        $obj->showTags = true;
        $obj->searchOnChannels = true;
        $obj->searchOnChannelsRowCount = 12;
        $obj->playVideoOnFullscreen = false;
        $obj->playVideoOnBrowserFullscreen = false;
        $obj->filterUserChannel = false;
        return $obj;
    }
  
    public function getHelp(){
        if(User::isAdmin()){
            return "<h2 id='LifeStreamLayout help'>LifeStreamLayout options (admin)</h2><table class='table'><thead><th>Option-name</th><th>Default</th><th>Description</th></thead><tbody><tr><td>BigVideo</td><td>checked</td><td>Create a big preview with a direct description on top</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>checked,checked,checked,unchecked</td><td>Metacategories</td></tr><tr><td>SubCategorys</td><td>unchecked</td> <td>Enable a view for subcategories on top</td></tr><tr><td>Description</td><td>unchecked</td><td>Enable a small button for show the description</td></tr></tbody></table>";   
        }
        return "";
    }
    public function getFirstPage(){
        global $global;
        if(!YouPHPTubePlugin::isEnabled("d3sa2k4l3-23rds421-re323-4ae-423")){
            return $global['systemRootPath'].'plugin/LifeStreamLayout/view/modeLifeStreamLayout.php';
        }
    }   
    
    public function getTags() {
        return array('free', 'firstPage', 'LifeStreamLayout');
    }
    
    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;
        
        $js = '';
        if(!empty($obj->playVideoOnFullscreen)){
            $js = '<script src="' . $global['webSiteRootURL'] . 'plugin/LifeStreamLayout/fullscreen.js"></script>';
        }
        if(!empty($obj->playVideoOnBrowserFullscreen)){
            $js = '<script>var playVideoOnBrowserFullscreen = 1;</script>';
        }
        return $js;
    }
    
    public function getChannel($user_id, $user){
        global $global;
        include $global['systemRootPath'].'plugin/LifeStreamLayout/view/modeLifeStreamChannelLayout.php';
    }
    
}
