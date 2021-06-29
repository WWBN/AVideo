<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'objects/video.php';

class MobileManager extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$MOBILE
        );
    }
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
        return "1.5";
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
        $obj->hideTabChat2 = false;
        $obj->hideTabPlayLists = false;
        $obj->hideViewsCounter = false;
        $obj->hideLikes = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "This Software must be used for Good, never Evil. There is no tolerance for objectionable content or abusive users. It is expressly forbidden to use this app to build porn sites, violence, racism or anything else that affects human integrity or denigrates the image of anyone.\n"
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
        $obj->disableComments = false;
        $obj->approvalMode = false;
        $obj->showMeet = true;
        $obj->goLiveWithMeet = true;
        $obj->doNotAutoSearch = false;
        $obj->playStoreApp = 'https://play.google.com/store/apps/details?id=mobile.youphptube.com';
        $obj->appleStoreApp = 'https://apps.apple.com/us/app/youphptube/id1337322357';
        
        $obj->pwa_background_color = "#000000";
        $o = new stdClass();
        $o->type = array('fullscreen', 'standalone', 'minimal-ui');
        $o->value = "standalone";
        
        $obj->pwa_display = $o;
        $obj->pwa_scope = "/";

        return $obj;
    }

    public function upload(){
    }

}
