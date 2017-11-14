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
    
    public function getFooterCode(){
        return "";
    }
    public function getHeadCode(){
        return "";
    }
    public function getHTMLBody(){
        return "";
    }
    public function getHTMLMenuLeft(){
        return "";
    }
    public function getHTMLMenuRight() {
        return "";
    }
    
    public function getPluginMenu(){
        return "";
    }
    
    public function getVideosManagerListButton(){
        return "";
    }
    
    public function getDataObject(){
        $obj = Plugin::getPluginByUUID($this->getUUID());
        $o = json_decode($obj['object_data']);
        $eo = $this->getEmptyDataObject();
        //var_dump($obj['object_data'], $o);
        return (object) array_merge((array) $eo, (array) $o);
    }
    
    public function getEmptyDataObject(){
        $obj = new stdClass();
        return $obj;
    }
    
    public function getFirstPage(){
        return false;
    }

}
