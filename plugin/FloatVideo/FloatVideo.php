<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class FloatVideo extends PluginAbstract {

    public function getTags() {
        return array(
            PluginTags::$FREE,
            PluginTags::$PLAYER,
            PluginTags::$LAYOUT,
        );
    }
    public function getDescription() {
        return "Enable Or disable Float Video";
    }

    public function getName() {
        return "FloatVideo";
    }

    public function getUUID() {
        return "ecb173e1-bd3e-45a7-89f1-e6df023e64a3";
    }
    
    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->doNotFloatVideo = true;
        return $obj;
    }

    public function getHeadCode() {
        global $global;
        $str = "";
        if(isVideo()){
            $o = $this->getDataObject();
            if(empty($o->doNotFloatVideo)){
                $str .= "<style> ".(file_get_contents($global['systemRootPath'] . 'plugin/FloatVideo/floatVideo.css'))."</style>";
            }
        }
        return $str;   
    }
    
    public function getFooterCode() {
        global $global;
        $str = "";
        if(isVideo()){
            $o = $this->getDataObject();
            if(empty($o->doNotFloatVideo)){
                $str .= "<script> ".(file_get_contents($global['systemRootPath'] . 'plugin/FloatVideo/floatVideo.js'))."</script>";
            }
        }
        return $str;        
    }
}
