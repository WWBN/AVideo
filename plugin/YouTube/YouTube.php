<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
class YouTube extends PluginAbstract {

    public function getDescription() {
        return "Make the first page works as a YouTube";
    }

    public function getName() {
        return "YouTube";
    }

    public function getUUID() {
        return "youu05bf-3570-4b1f-977a-fd0e5cabtube";
    }

    public function getPluginVersion() {
        return "1.0";   
    }

    public function getHeadCode() {
        global $global, $sidebarStyle;
        $obj = $this->getDataObject();
        // preload image
        $js = "<script>var img1 = new Image();img1.src=\"{$global['webSiteRootURL']}view/img/video-placeholder.png\";</script>";
        $css = '<link href="' . $global['webSiteRootURL'] . 'plugin/YouTube/style.css" rel="stylesheet" type="text/css"/>';
        if(!empty($obj->playVideoOnFullscreen) && !empty($_GET['videoName'])){
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/YouPHPFlix2/view/css/fullscreen.css" rel="stylesheet" type="text/css"/>';
        }
        if(!empty($obj->playVideoOnFullscreen)){
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        
        $sidebarStyle = ($this->menuIsOpen())?"/**/":"display: none;";
        
        return $js.$css;
    }
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        
        $obj->hidePrivateVideos = false;
        $obj->BigVideo = true;
        $obj->Description = false;
        $obj->CategoryDescription = false;
        $obj->Trending = true;
        $obj->TrendingCustomTitle = "";
        $obj->TrendingRowCount = 12;
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
        $obj->Categories = false;
        $obj->CategoriesRowCount = 12;
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
            return "<h2 id='YouTube help'>YouTube options (admin)</h2><table class='table'><thead><th>Option-name</th><th>Default</th><th>Description</th></thead><tbody><tr><td>BigVideo</td><td>checked</td><td>Create a big preview with a direct description on top</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>checked,checked,checked,unchecked</td><td>Metacategories</td></tr><tr><td>SubCategorys</td><td>unchecked</td> <td>Enable a view for subcategories on top</td></tr><tr><td>Description</td><td>unchecked</td><td>Enable a small button for show the description</td></tr></tbody></table>";   
        }
        return "";
    }
    public function getFirstPage(){
        global $global;
        return $global['systemRootPath'].'plugin/YouTube/view/modeYouTube.php';
    }   
    
    public function getTags() {
        return array('free', 'firstPage', 'YouTube');
    }
    
    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;
        
        $js = '<script src="' . $global['webSiteRootURL'] . 'plugin/YouTube/script.js"></script>';
        if(!empty($obj->playVideoOnFullscreen)){
            $js = '<script src="' . $global['webSiteRootURL'] . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
        }
        if(!empty($obj->playVideoOnBrowserFullscreen)){
            $js = '<script>var playVideoOnBrowserFullscreen = 1;</script>';
        }
        return $js;
    }
    
    public function navBar() {
        global $global,$includeDefaultNavBar;
        //include $global['systemRootPath'] . 'plugin/YouTube/view/navbar.php';
        //$includeDefaultNavBar = false;
    }
    
    public function getStart() {
        global $global;
        $global['bodyClass'] = ($this->menuIsOpen())?"youtube":""; 
        
    }
    
    private function menuIsOpen(){
        return (empty($_COOKIE['youTubeMenuIsOpened']) || $_COOKIE['youTubeMenuIsOpened']==="false")?false:true;
    }
}
