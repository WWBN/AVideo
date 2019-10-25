<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class AdsForJesus extends PluginAbstract {

    public function getDescription() {
        $txt = " We will provide a simple VMAP Ad link for free, these ads will be placed in your videos.<br>"
                . "This will give your users the greatest wisdom of all, as well as invaluable value. <i class=\"fas fa-pray\"> </i> <i class=\"fas fa-cross fa-2x \"></i>";
        
        return $txt ;
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
        if(empty($obj->topMenuLink)){
            return '';
        }
        return '<li>
        <a href="https://forjesus.tv/"  class="btn btn-success navbar-btn" data-toggle="tooltip" title="For jesus TV" data-placement="bottom" >
            <i class="fas fa-cross"></i>  <span class="hidden-md hidden-sm">ForJesus.TV Site</span>
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
                $showAds = YouPHPTubePlugin::showAds($video['id']);
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

    public function getFooterCode() {
        $js = ''; 
        //if (!empty($_GET['videoName']) || !empty($_GET['u'])) {
        if (!empty($_GET['videoName'])) {
            if (empty($_GET['u'])) {
                $video = Video::getVideoFromCleanTitle($_GET['videoName']);
                $showAds = YouPHPTubePlugin::showAds($video['id']);
                if (!$showAds) {
                    return "";
                }
            } else {
                $video['duration'] = "01:00:00";
                $_GET['videoName'] = "Live-" . uniqid();
            }
            global $global;

            $video_length = parseDurationToSeconds($video['duration']);
            $obj = $this->getDataObject();
            
            $js .= '<script src="//imasdk.googleapis.com/js/sdkloader/ima3.js"></script>';
            $js .= '<script src="' . $global['webSiteRootURL'] . 'js/videojs-contrib-ads/videojs.ads.js" type="text/javascript"></script>
            <script src="' . $global['webSiteRootURL'] . 'plugin/AD_Server/videojs-ima/videojs.ima.js" type="text/javascript"></script>';
            
            $js .= "<script>
var player = videojs('mainVideo'".PlayerSkins::getDataSetup().");
var options = {
    id: 'mainVideo',
    adTagUrl: 'https://forjesus.tv/vmap.xml?video_durarion={$video_length}&start={$obj->start}&mid25Percent={$obj->mid25Percent}&mid50Percent={$obj->mid50Percent}&mid75Percent={$obj->mid75Percent}&end={$obj->end}'
};
try{
player.ima(options);
}catch(e){}
</script>";
            $js .= "
    <script>
function fixAdSize(){
    ad_container = $('#mainVideo_ima-ad-container');
    if(ad_container.length){
        height = ad_container.css('height');
        width = ad_container.css('width');
        $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'height': height});
        $($('#mainVideo_ima-ad-container div:first-child')[0]).css({'width': width});
    }
}
$(function () {
    // Remove controls from the player on iPad to stop native controls from stealing
    // our click
    var contentPlayer = document.getElementById('content_video_html5_api');
    if ((navigator.userAgent.match(/iPad/i) ||
            navigator.userAgent.match(/Android/i)) &&
            contentPlayer.hasAttribute('controls')) {
        contentPlayer.removeAttribute('controls');
    }

    // Initialize the ad container when the video player is clicked, but only the
    // first time it's clicked.
    var startEvent = 'click';
    if (navigator.userAgent.match(/iPhone/i) ||
            navigator.userAgent.match(/iPad/i) ||
            navigator.userAgent.match(/Android/i)) {
        startEvent = 'touchend';
    }
    if (typeof player !== 'undefined') {
        player.one(startEvent, function () {
            player.ima.initializeAdDisplayContainer();
        });
    }else{
        setTimeout(function(){
            if (typeof player !== 'undefined') {
                player.one(startEvent, function () {
                    player.ima.initializeAdDisplayContainer();
                });
            }
        },2000);
    }    
    setInterval(function(){ fixAdSize(); }, 300);
});
</script>";
        }
        return $js;
    }

}
