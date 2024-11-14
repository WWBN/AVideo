<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

{includeTables}

class {pluginName} extends PluginAbstract {

    public function getDescription() {
        $desc = "{pluginName} Plugin";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        //$help = "<br><small><a href='' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $desc;
    }

    public function getName() {
        return "{pluginName}";
    }

    public function getUUID() {
        return "{pluginName}-{uid}";
    }

    public function getPluginVersion() {
        return "1.0";
    }

    public function updateScript() {
        global $global;       
        /*
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            sqlDal::executeFile($global['systemRootPath'] . 'plugin/PayPerView/install/updateV2.0.sql');
        }
         * 
         */
        return true;
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        /*
        $obj->textSample = "text";
        $obj->checkboxSample = true;
        $obj->numberSample = 5;
        
        $o = new stdClass();
        $o->type = array(0=>__("Default"))+array(1,2,3);
        $o->value = 0;
        $obj->selectBoxSample = $o;
        
        $o = new stdClass();
        $o->type = "textarea";
        $o->value = "";
        $obj->textareaSample = $o;
        */
        return $obj;
    }
    
    
    public function getPluginMenu() {
        global $global;
        return '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/{pluginName}/View/editor.php\')" class="btn btn-primary btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';        
    }

}
