<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class FloatVideo extends PluginAbstract {

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

    public function getFooterCode() {
        $o = $this->getDataObject();
        $str = "<script> doNotFloatVideo = ".($o->doNotFloatVideo?"true":"false").";</script>";
        return $str;        
    }
    
    public function getTags() {
        return array('free');
    }
}
