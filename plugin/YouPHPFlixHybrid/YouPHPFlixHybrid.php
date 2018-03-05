<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class YouPHPFlixHybrid extends PluginAbstract {

    public function getDescription() {
        return "This is a merge of YouPHPFlix-Plugin and the Gallery-Plugin, which are also dependencies. <br /> For a proper work of the plugin, disable YouPHPFlix and Gallery, but do not remove them. Else, there will be small style-issues.";
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
        $obj->pageDots = true;
        return $obj;
    }
        
    public function getFirstPage(){
        global $global;        
        if(("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL'])||("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL'])){
            return $global['systemRootPath'].'plugin/YouPHPFlix/view/firstPage.php';
        }
        else {
            return $global['systemRootPath'].'plugin/Gallery/view/modeGallery.php';
        }
    }   
        
    public function getHeadCode() {
        global $global;
        $obj = $this->getDataObject();
        
        // When first page at all
        if(("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL'])||("https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']==$global['webSiteRootURL'])){
            $youphpflixTmp = "<link href=\"{$global['webSiteRootURL']}plugin/YouPHPFlix/view/css/style.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            return $youphpflixTmp;
        } else {
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
