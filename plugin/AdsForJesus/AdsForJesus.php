<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class AdsForJesus extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$ADS,
            PluginTags::$FREE,
            PluginTags::$PLAYER,
        );
    }

    public function getDescription() {
        $txt = " We will provide a simple VMAP Ad link for free, these ads will be placed in your videos.<br>"
                . "This will give your users the greatest wisdom of all, as well as invaluable value. <i class=\"fas fa-pray\"> </i> <i class=\"fas fa-cross fa-2x \"></i>";

        return $txt;
    }

    public function getName() {
        return "AdsForJesus";
    }

    public function getUUID() {
        return "AdsForJesus-43a9-479b-994a-5430dc22958c";
    }

    public function getPluginMenu() {
        global $global;
        return "<a href='https://forjesus.tv/' target='__blank' class='btn btn-success'><img src='https://forjesus.tv/img/logoLandscape-50.png' alt='4JesusTV' class='img img-responsive'></a>";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->start = true;
        $obj->mid25Percent = true;
        $obj->mid50Percent = true;
        $obj->mid75Percent = true;
        $obj->end = true;

        $obj->topMenuLink = false;

        return $obj;
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if (empty($obj->topMenuLink)) {
            return '';
        }
        return '<li>
        <a href="https://forjesus.tv/"  class="btn btn-success navbar-btn" data-toggle="tooltip" title="For jesus TV" data-placement="bottom" >
            <i class="fas fa-cross"></i>  <span class="hidden-md hidden-sm hidden-mdx">ForJesus.TV Site</span>
        </a>
    </li>';
    }

    public function getHeadCode() {
        $js = '';
        $css = '';
        //if (!empty($_GET['videoName']) || !empty($_GET['u'])) {
        if (!empty($_GET['videoName'])) {
            if (empty($_GET['u'])) {
                $video = Video::getVideoFromCleanTitle($_GET['videoName']);
                $showAds = AVideoPlugin::showAds($video['id']);
                if (!$showAds) {
                    return "";
                }
            }
            global $global;
            $css .= '<link href="' . $global['webSiteRootURL'] . 'plugin/AD_Server/videojs-ima/videojs.ima.css" rel="stylesheet" type="text/css"/>';
            $css .= '<style>.ima-ad-container{z-index:1000 !important;}</style>';
        }
        return $js . $css;
    }

    public function afterVideoJS() {
        global $global;
        $js = '';
        $js .= '<script src="//imasdk.googleapis.com/js/sdkloader/ima3.js"></script>';
        $js .= '<script src="' . $global['webSiteRootURL'] . 'js/videojs-contrib-ads/videojs.ads.js" type="text/javascript"></script>';
        $js .= '<script src="' . $global['webSiteRootURL'] . 'plugin/AD_Server/videojs-ima/videojs.ima.js" type="text/javascript"></script>';

        //if (!empty($_GET['videoName']) || !empty($_GET['u'])) {
        if (!empty($_GET['videoName'])) {
            if (empty($_GET['u'])) {
                $video = Video::getVideoFromCleanTitle($_GET['videoName']);
                $showAds = AVideoPlugin::showAds($video['id']);
                if (!$showAds) {
                    return "";
                }
            } else {
                $video['duration'] = "01:00:00";
                $_GET['videoName'] = "Live-" . uniqid();
            }

            $video_length = parseDurationToSeconds($video['duration']);
            $obj = $this->getDataObject();
            PlayerSkins::setIMAADTag("https://forjesus.tv/vmap.xml?video_durarion={$video_length}&start={$obj->start}&mid25Percent={$obj->mid25Percent}&mid50Percent={$obj->mid50Percent}&mid75Percent={$obj->mid75Percent}&end={$obj->end}");
        }else if(isLive()){
            //PlayerSkins::setIMAADTag("https://forjesus.tv/vmap.xml?video_durarion=0&start=1&mid25Percent=0&mid50Percent=0&mid75Percent=0&end=1");
        }
        
        
        return $js;
    }

}
