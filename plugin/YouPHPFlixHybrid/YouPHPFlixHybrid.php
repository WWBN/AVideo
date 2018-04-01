<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class YouPHPFlixHybrid extends PluginAbstract {

    public function getDescription() {
        return "This is a merge of YouPHPFlix-Plugin and the Gallery-Plugin, which are also dependencies. <br />Activate the other plugins and set the parameters of them as well.<br />Author: <a href='http://hersche.github.io' target='_blank' >Vinzenz Hersche</a>";
    }

    public function getName() {
        return "YouPHPFlixHybrid";
    }

    public function getUUID() {
        return "d3sa2k4l3-23rds421-re323-4ae-423";
    }
    
    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        return $obj;
    }
        
    public function getFirstPage(){
        global $global; 
        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(("http://".$url===$global['webSiteRootURL'])||("https://".$url===$global['webSiteRootURL'])){
            return $global['systemRootPath'].'plugin/YouPHPFlix/view/firstPage.php';
        }
        else {
            return $global['systemRootPath'].'plugin/Gallery/view/modeGallery.php';
        }
    }   
        
    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        // When first page at all
        if((("http://".$url===$global['webSiteRootURL'])||("https://".$url===$global['webSiteRootURL']))&&YouPHPTubePlugin::isEnabled("e2a568e6-ef61-4dcc-aad0-0109e9be8e36")===false){
            $youphpflixTmp = "<link href=\"{$global['webSiteRootURL']}plugin/YouPHPFlix/view/css/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            return $youphpflixTmp;
        } else if (YouPHPTubePlugin::isEnabled("a06505bf-3570-4b1f-977a-fd0e5cab205d")==false) {
        // When category or search or everything else is browsed
            $galleryTmp = "<script>var img1 = new Image();img1.src=\"{$global['webSiteRootURL']}view/img/video-placeholder.png\";</script>";
            $galleryTmp .= '<link href="' . $global['webSiteRootURL'] . 'plugin/Gallery/style.css" rel="stylesheet" type="text/css"/>';
            return $galleryTmp;
        }
    }
    
    public function getTags() {
        return array('free', 'firstPage', 'netflix', 'gallery', 'fork');
    }

    
}
