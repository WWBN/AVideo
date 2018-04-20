<?php

require_once $global['systemRootPath'] . 'objects/plugin.php';

abstract class PluginAbstract {

    /**
     * return the universally unique identifier (UUID) is a 128-bit number used to identify information in computer systems
     * if you not sure get one here https://www.uuidgenerator.net/
     */
    abstract function getUUID();

    /**
     * return the name ot the plugin
     */
    abstract function getName();

    /**
     * return the description of the plugin
     */
    abstract function getDescription();

    public function getFooterCode() {
        return "";
    }

    public function getHeadCode() {
        return "";
    }

    public function getHTMLBody() {
        return "";
    }

    public function getHTMLMenuLeft() {
        return "";
    }

    public function getHTMLMenuRight() {
        return "";
    }

    public function getPluginMenu() {
        return "";
    }

    public function getVideosManagerListButton() {
        return "";
    }

    public function getTags() {
        
    }
    
    public function getGallerySection(){
        return "";
    }

    public function getDataObject() {
        $obj = Plugin::getPluginByUUID($this->getUUID());
        //echo $obj['object_data'];
        $o = json_decode($obj['object_data']);
        $eo = $this->getEmptyDataObject();
        //var_dump($obj['object_data']);
        //var_dump($eo, $o, (object) array_merge((array) $eo, (array) $o));exit;
        return (object) array_merge((array) $eo, (array) $o);
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        return $obj;
    }

    public function getFirstPage() {
        return false;
    }
    
    public function afterNewVideo($videos_id) {
        return false;
    }
    
    public function afterNewComment($comments_id) {
        return false;
    }
    
    public function afterNewResponse($comments_id) {
        return false;
    }
    
    public function xsendfilePreVideoPlay(){
        return false;
    }  
    
    public function getLogin() {
        $obj = new stdClass();
        $obj->class = ""; // btn btn-primary btn-block
        $obj->icon = ""; // fa fa-facebook-square
        $obj->type = ""; // Facebook, Google, etc
        $obj->linkToDevelopersPage = ""; //https://console.developers.google.com/apis/credentials , https://developers.facebook.com/apps
        
        return $obj;
    }
    
    public function getWatchActionButton(){
        return "";
    }
    
    public function getStart() {
        return false;
    }
    
    public function getEnd() {
        return false;
    }
    
    public function canEditPlugin(){
        global $global;
        return empty($global['disableAdvancedConfigurations']);
    }
    
    public function hidePlugin(){
        return false;
    }
    
    public function getChannelButton(){
        return "";
    }
    

}
