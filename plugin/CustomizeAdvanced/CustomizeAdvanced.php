<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CustomizeAdvanced extends PluginAbstract {

    public function getDescription() {
        return "Fine Tuning your YouPHPTube";
    }

    public function getName() {
        return "CustomizeAdvanced";
    }

    public function getUUID() {
        return "55a4fa56-8a30-48d4-a0fb-8aa6b3f69033";
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->doNotShowUploadMP4Button = true;
        $obj->doNotShowEncoderButton = false;
        $obj->doNotShowEmbedButton = false;
        $obj->doNotShowEncoderResolutionLow = false;
        $obj->doNotShowEncoderResolutionSD = false;
        $obj->doNotShowEncoderResolutionHD = false;
        $obj->doNotShowLeftMenuAudioAndVideoButtons = false;
        $obj->disableNativeSignUp = false;
        $obj->disableNativeSignIn = false;
        $obj->doNotShowWebsiteOnContactForm = false;
        $obj->newUsersCanStream = false;
        $obj->doNotIndentifyByEmail = false;
        $obj->doNotIndentifyByName = false;
        $obj->doNotIndentifyByUserName = false;
        $obj->doNotUseXsendFile = false;
        $obj->makeVideosInactiveAfterEncode = false;
        $obj->usePermalinks = false;
        $obj->showAdsenseBannerOnTop = false;
        $obj->showAdsenseBannerOnLeft = true;
        $obj->disableAnimatedGif = false;
        $obj->unverifiedEmailsCanNOTLogin = false;
        $obj->removeBrowserChannelLinkFromMenu = false;
        $obj->uploadButtonDropdownIcon = "fas fa-video";
        $obj->uploadButtonDropdownText = "";
        $obj->EnableWavesurfer = true;
        $obj->EnableMinifyJS = false;
        $obj->disableShareAndPlaylist = false;
        $obj->commentsMaxLength = 200;
        $obj->disableYoutubePlayerIntegration = false;
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";        
        $obj->underMenuBarHTMLCode = $o;// an url for encoder network
        $obj->encoderNetwork = "";// an url for encoder network
        return $obj;
    }
    
    public function getTags() {
        return array('free', 'customization', 'buttons', 'resolutions');
    }
}
