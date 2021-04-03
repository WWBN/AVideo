<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

class Gallery extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
            PluginTags::$GALLERY,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription() {
        return "Make the first page works as a gallery";
    }

    public function getName() {
        return "Gallery";
    }

    public function getUUID() {
        return "a06505bf-3570-4b1f-977a-fd0e5cab205d";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        // preload image
        $js = "<script>var img1 = new Image();img1.src=\"".getCDN()."view/img/video-placeholder-gray.png\";</script>";
        $css = '<link href="' . getCDN() . 'plugin/Gallery/style.css?' . (filemtime($global['systemRootPath'] . 'plugin/Gallery/style.css')) . '" rel="stylesheet" type="text/css"/>';

        if (!empty($obj->playVideoOnFullscreenOnIframe)) {
            if (canFullScreen()) {
                $css .= '<link href="' . getCDN() . 'plugin/YouPHPFlix2/view/css/fullscreen.css" rel="stylesheet" type="text/css"/>';
                $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
                $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
            }
            $js .= '<script>var playVideoOnFullscreen = true</script>';
        } else if (!empty($obj->playVideoOnFullscreen) && canFullScreen()) {
            $css .= '<link href="' . getCDN() . 'plugin/Gallery/fullscreen.css" rel="stylesheet" type="text/css"/>';
        }
        if (!empty($obj->playVideoOnFullscreen)) {
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }

        return $js . $css;
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->hidePrivateVideos = false;
        $obj->BigVideo = true;
        $obj->useSuggestedVideosAsCarouselInBigVideo = true;
        $obj->GifOnBigVideo = true;
        $obj->Description = false;
        $obj->CategoryDescription = false;

        $obj->Suggested = true;
        $obj->SuggestedCustomTitle = "";
        $obj->SuggestedRowCount = 12;
        $obj->SuggestedOrder = 1;

        $obj->Trending = true;
        $obj->TrendingCustomTitle = "";
        $obj->TrendingRowCount = 12;
        $obj->TrendingOrder = 2;

        $obj->DateAdded = true;
        $obj->DateAddedCustomTitle = "";
        $obj->DateAddedRowCount = 12;
        $obj->DateAddedOrder = 3;
        
        $obj->MostWatched = true;
        $obj->MostWatchedCustomTitle = "";
        $obj->MostWatchedRowCount = 12;
        $obj->MostWatchedOrder = 4;
        
        $obj->MostPopular = true;
        $obj->MostPopularCustomTitle = "";
        $obj->MostPopularRowCount = 12;
        $obj->MostPopularOrder = 5;
        
        $obj->SortByName = false;
        $obj->SortByNameCustomTitle = "";
        $obj->SortByNameRowCount = 12;
        $obj->SortByNameOrder = 6;
        
        $obj->SubscribedChannels = true;
        $obj->SubscribedChannelsRowCount = 12;
        $obj->SubscribedChannelsOrder = 7;
        
        $obj->Categories = true;
        $obj->CategoriesCustomTitle = "";
        $obj->CategoriesRowCount = 12;
        $obj->CategoriesOrder = 7;
        
        $obj->sortReverseable = false;
        $obj->SubCategorys = false;
        $obj->showTags = true;
        $obj->showCategoryTag = true;
        $obj->showCategoryLiveRow = false;
        $obj->searchOnChannels = true;
        $obj->searchOnChannelsRowCount = 12;
        $obj->playVideoOnFullscreen = false;
        $obj->playVideoOnFullscreenOnIframe = false;
        $obj->playVideoOnBrowserFullscreen = false;
        $obj->filterUserChannel = false;
        $obj->screenColsLarge = 6;
        $obj->screenColsMedium = 3;
        $obj->screenColsSmall = 2;
        $obj->screenColsXSmall = 1;
        $obj->allowSwitchTheme = true;
        self::addDataObjectHelper('allowSwitchTheme', 'Show Switch theme button');
        $themes = getThemes();
        foreach ($themes as $value) {
            $name = ucfirst($value);
            eval('$obj->SwitchThemeShow'.$name.' = true;');
            self::addDataObjectHelper('SwitchThemeShow'.$name, 'Show '.$name.' Option', 'Uncheck this button to not show the '.$name.' in your themes list');
            eval('$obj->SwitchThemeLabel'.$name.' = "'.$name.'";');
            self::addDataObjectHelper('SwitchThemeLabel'.$name, $name.' Theme Label', 'Change the label name to the theme '.$name.' in your themes list');
        }

        return $obj;
    }

    public function navBarProfileButtons() {
        global $global;
        $navBarButtons = 0;
        $obj = $this->getDataObject();
        if ($obj->allowSwitchTheme) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/themeSwitcher.php';
        }
    }
    
    public function navBarButtons() {
        global $global;
        $navBarButtons = 1;
        $obj = $this->getDataObject();
        if (!empty($obj->allowSwitchTheme)) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/themeSwitcher.php';
        }
    }

    public function getHelp() {
        if (User::isAdmin()) {
            return "<h2 id='Gallery help'>" . __('Gallery options (admin)') . "</h2><table class='table'><thead><th>" . __('Option-name') . "</th><th>" . __('Default') . "</th><th>" . __('Description') . "</th></thead><tbody><tr><td>BigVideo</td><td>" . __('checked') . "</td><td>" . __('Create a big preview with a direct description on top') . "</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>" . __('checked') . "," . __('checked') . "," . __('checked') . "," . __('unchecked') . "</td><td>" . __('Metacategories') . "</td></tr><tr><td>SubCategorys</td><td>" . __('unchecked') . "</td> <td>" . __('Enable a view for subcategories on top') . "</td></tr><tr><td>Description</td><td>" . __('unchecked') . "</td><td>" . __('Enable a small button for show the description') . "</td></tr></tbody></table>";
        }
        return "";
    }

    public function getFirstPage() {
        global $global;
        if (!AVideoPlugin::isEnabledByName("YouPHPFlix2") && !AVideoPlugin::isEnabledByName("CombineSites")) {
            return $global['systemRootPath'] . 'plugin/Gallery/view/modeGallery.php';
        }
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        global $global;

        $js = '';
        if (!empty($obj->playVideoOnFullscreenOnIframe)) {
            $js = '<script src="' . getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
            $js .= '<script>$(function () { if(typeof linksToFullscreen === \'function\'){ linksToFullscreen(\'a.galleryLink\'); } });</script>';
        } else
        if (!empty($obj->playVideoOnFullscreen)) {
            $js = '<script src="' . getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
        } else
        if (!empty($obj->playVideoOnBrowserFullscreen)) {
            $js = '<script src="' . getCDN() . 'plugin/YouPHPFlix2/view/js/fullscreen.js"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
            $js .= '<script src="' . getCDN() . 'plugin/Gallery/fullscreen.js"></script>';
            $js .= '<script>var playVideoOnBrowserFullscreen = 1;</script>';
        }
        return $js;
    }
    
    static function getThemes(){
        $obj = AVideoPlugin::getDataObject("Gallery");
        if(empty($obj->allowSwitchTheme)){
           return false; 
        }
        $themes = getThemes();
        $selectedThemes = array();
        foreach ($themes as $value) {
            $name = ucfirst($value);
            eval('$t = $obj->SwitchThemeShow'.$name.';');
            if(!empty($t)){
                eval('$l = $obj->SwitchThemeLabel'.$name.';');
                $selectedThemes[] = array('name'=>$value,'label'=>$l);
            }
        }
        return $selectedThemes;
    }
    
    static function getSectionsOrder(){
        $obj = AVideoPlugin::getObjectData('Gallery');
        $sections = array();
        foreach ($obj as $key => $value) {
            if(preg_match('/(.*)Order$/', $key, $matches)){
                $index = $value;
                while(isset($sections[$index])){
                    $index++;
                }
                $sections[$index] = array('name'=>$matches[1], 'active'=>$obj->{$matches[1]});
            }
        }
        ksort($sections);
        return $sections;
        
    }
    
    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframeSmall(webSiteRootURL+\'plugin/Gallery/view/sections.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-sort-numeric-down"></i> ' . __('Sort Sections') . '</button>';
    }

}
