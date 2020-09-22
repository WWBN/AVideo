<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class ADs extends PluginAbstract {
    public function getTags() {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$ADS,
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        $txt = "Handle the ads system, like Adsense or similar";
        //$help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/AD_Overlay-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        $help = "";
        return $txt . $help;
    }

    public function getName() {
        return "ADs";
    }

    public function getUUID() {
        return "ADs73225-3807-4167-ba81-0509dd280e06";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global,$config;
        $obj = new stdClass();
        
        $adsense = $config->getAdsense();
        
        
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<center><img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'></center>":$adsense;;
        $obj->leaderBoardBigVideo = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample728x90.jpg'>":$adsense;
        $obj->leaderBoardTop = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample728x90.jpg'>":$adsense;
        $obj->leaderBoardTop2 = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample728x90.jpg'>":$adsense;
        $obj->channelLeaderBoardTop = $o;
        
        $o = new stdClass();        
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample728x90.jpg'>":$adsense;;
        $obj->leaderBoardMiddle = $o;
                
        /*
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample728x90.jpg'>":$adsense;;
        $obj->leaderBoardFooter = $o;
         * 
         */
        
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;;
        $obj->sideRectangle = $o;
        
        
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<center><img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'></center>":$adsense;;
        $obj->leaderBoardBigVideoMobile = $o;

        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;;
        $obj->leaderBoardTopMobile = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;;
        $obj->leaderBoardTopMobile2 = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;
        $obj->channelLeaderBoardTopMobile = $o;
                
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;;
        $obj->leaderBoardMiddleMobile = $o;
        /*
        $o = new stdClass();       
        $o->type = "textarea";
        $o->value = empty($adsense)?"<img src='{$global['webSiteRootURL']}plugin/ADs/sample300x250.jpg'>":$adsense;
        $obj->leaderBoardFooterMobile = $o;
         * 
         */
        
        $obj->tags3rdParty = "<script> window.abkw = '{ChannelName},{Category}'; </script>";
        return $obj;
    }
    
    public function getHeadCode() {
        if(!empty($_GET['abkw'])){
            $abkw = preg_replace('/[^a-zA-Z0-9_ ,-]/', '',$_GET['abkw']);
            return "<script> window.abkw = '{$abkw}'; </script>";                    
        }
        $obj = $this->getDataObject();
        if(!empty($_GET['videoName'])){
            if(!empty($obj->tags3rdParty)){
                $v = Video::getVideoFromCleanTitle($_GET['videoName']);
                if(!empty($v)){            
                    $channelName = $v["channelName"];
                    $category = $v["category"];      
                    $tag = str_replace(array('{ChannelName}','{Category}'), array(addcslashes($channelName,"'"),  addcslashes($category,"'")), $obj->tags3rdParty);                    
                    
                    return $tag;
                }
            }
        }
        if(!empty($_GET['catName'])){
            if(!empty($obj->tags3rdParty)){
                $v = Category::getCategoryByName($_GET['catName']);
                if(!empty($v)){
                    $tag = str_replace(array(',','{ChannelName}','{Category}'), array('', '', addcslashes($v["name"],"'")), $obj->tags3rdParty);                    
                    return $tag;
                }
            }
        }
        if(!empty($_GET['channelName'])){
            if(!empty($obj->tags3rdParty)){
                $tag = str_replace(array(',','{ChannelName}','{Category}'), array('', addcslashes($_GET['channelName'],"'"), ''), $obj->tags3rdParty);                    
                return $tag;
            }
        }
        return "<script> window.abkw = 'home-page'; </script>";
    }
}