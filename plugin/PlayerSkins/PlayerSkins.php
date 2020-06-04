<?php

require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

class PlayerSkins extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Customize your playes Skin<br>The Skis options are: ";
        $dir = $global['systemRootPath'].'plugin/PlayerSkins/skins/';
        $names = array();
        foreach (glob($dir . '*.css') as $file) {
            $path_parts = pathinfo($file);
            $names[] = $path_parts['filename'];
        }
        return $desc."<code>".implode($names,"</code> or <code>")."</code>";
    }

    public function getName() {
        return "PlayerSkins";
    }

    public function getUUID() {
        return "e9a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->skin = "youtube";
        $obj->playbackRates = "[0.5, 1, 1.5, 2]";
        $obj->playerCustomDataSetup = "";
        $obj->showSocialShareOnEmbed = true;
        $obj->showLoopButton = true;
        $obj->showLogo = false;
        $obj->showLogoOnEmbed = false;
        $obj->showLogoAdjustScale = "0.4";
        $obj->showLogoAdjustLeft = "-74px";
        $obj->showLogoAdjustTop = "-22px;";
        return $obj;
    }

    public function getHeadCode() {
        global $global, $config;
        $obj = $this->getDataObject();
        $css = "";
        if (!empty($_GET['videoName']) || !empty($_GET['u'])  || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/skins/{$obj->skin}.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            if ($obj->showLoopButton && !isLive()) {
                $css .= "<link href=\"{$global['webSiteRootURL']}plugin/PlayerSkins/loopbutton.css\" rel=\"stylesheet\" type=\"text/css\"/>";
            }
            if($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo ){
                $logo = "{$global['webSiteRootURL']}".$config->getLogo(true);
                $css .= "<style>"
                        . ".player-logo{
  outline: none;
  filter: grayscale(100%);
  width:100px !important;
}
.player-logo:hover{
  filter: none;
}
.player-logo:before {
    display: inline-block;
    content: url({$logo});
    transform: scale({$obj->showLogoAdjustScale});
  position: relative;
  left:{$obj->showLogoAdjustLeft};
  top:{$obj->showLogoAdjustTop};
    
}"
                        . "</style>";
            }
        }
        return $css;
    }

    
    public function getFooterCode() {
        global $global, $config;
        $js = "";
        $obj = $this->getDataObject();
        if (!empty($_GET['videoName']) || !empty($_GET['u'])  || !empty($_GET['evideo']) || !empty($_GET['playlists_id'])) {
            if ($obj->showLoopButton && !isLive()) {
                $js .= "<script src=\"{$global['webSiteRootURL']}plugin/PlayerSkins/loopbutton.js\"></script>";
            }
            if($obj->showLogoOnEmbed && isEmbed() || $obj->showLogo ){
                $title = $config->getWebSiteTitle();
                $url = "{$global['webSiteRootURL']}{$config->getLogo(true)}";
                $js .= "<script>var PlayerSkinLogoTitle = '{$title}';</script>";
                $js .= "<script src=\"{$global['webSiteRootURL']}plugin/PlayerSkins/logo.js\"></script>";
            }
        }
        
        return $js;
    }
    
    public function getTags() {
        return array('free');
    }

    static function getDataSetup($str = ""){
        global $video, $disableYoutubeIntegration, $global;
        $obj = AVideoPlugin::getObjectData('PlayerSkins');
        
        $dataSetup = array();
        
        if(!empty($obj->playbackRates)){
            $dataSetup[] = "'playbackRates':{$obj->playbackRates}";
        }
        if ((isset($_GET['isEmbedded'])) && ($disableYoutubeIntegration == false) && !empty($video['videoLink'])) {
            if ($_GET['isEmbedded'] == "y") {
                $dataSetup[] = "techOrder:[\"youtube\"]";
                $dataSetup[] = "sources:[{type: \"video/youtube\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "youtube:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            } else if ($_GET['isEmbedded'] == "v") {
                $dataSetup[] = "techOrder:[\"vimeo\"]";
                $dataSetup[] = "sources:[{type: \"video/vimeo\", src: \"{$video['videoLink']}\"}]";
                $dataSetup[] = "vimeo:{customVars: {wmode: \"transparent\", origin: \"{$global['webSiteRootURL']}\"}}";
            }
        }
        
        if(!empty($dataSetup)){
            return ",{". implode(",", $dataSetup)."{$str}{$obj->playerCustomDataSetup}}";
        }
        
        return "";
    }
    
    
}
