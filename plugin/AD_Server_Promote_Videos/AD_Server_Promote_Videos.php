<?php

header('Access-Control-Allow-Origin: *');
/**
 * https://support.google.com/adsense/answer/4455881
 * https://support.google.com/adsense/answer/1705822
 * AdSense for video: Publisher Approval Form
 * https://services.google.com/fb/forms/afvapproval/
 */
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AD_Server/Objects/VastCampaigns.php';

class AD_Server_Promote_Videos extends PluginAbstract {

    public function getDescription() {
        $desc = "VAST Ad Server Automatic Promote Video<br>";
        
        $desc .= $this->isReadyLabel(array('AD_Server','YPTWallet','AD_Server_Promote_Videos','AD_Server_Location'));        
        
        return $desc;
    }

    public function getName() {
        return "AD_Server_Promote_Videos";
    }

    public function getUUID() {
        return "357c8967-553d-499b-b771-1059d02adde4";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->pricePerPrint = 0.001;
        $obj->minimumPrintAmount = 1000;
        $obj->minimumPrice = 1;
        $obj->step = 10;
        $obj->expireIn = "1 year";
        return $obj;
    }
    
    public function getVideosManagerListButton() {
        $obj = $this->getDataObject();
        $btn = '<br><button type="button" class="btn btn-success btn-sm btn-xs " onclick="promote(\' + row.id + \');" data-row-id="right"  data-toggle="tooltip" data-placement="left" title="test"><span class="fa fa-star " aria-hidden="true"></span> Promote Video</button>';
        return $btn;
    }
    
    public function getHeadCode() {
        global $global, $isMyChannel;
        $js = "";
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if ($baseName === 'managerVideos.php') {
            $js = '<link href="' . $global['webSiteRootURL'] . 'view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>';
            $content = file_get_contents("{$global['systemRootPath']}plugin/AD_Server_Promote_Videos/head.js");
            $js .= "<script>{$content}</script>";
        }
        return $js;
    }
    
    public function getFooterCode() {
        global $global;
        $content = "";
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if ($baseName === 'managerVideos.php' || $baseName === 'channel.php') {
            $content = '<script src="' . $global['webSiteRootURL'] . 'view/js/jquery-ui/jquery-ui.js" type="text/javascript"></script>';
            $content .= file_get_contents("{$global['systemRootPath']}plugin/AD_Server_Promote_Videos/footer.html");
        }
        
        return $content;
    }


}