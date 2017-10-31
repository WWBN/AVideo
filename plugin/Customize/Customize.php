<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Customize extends PluginAbstract {

    public function getDescription() {
        return "Customize About menu item, page Footer and Description metatag";
    }

    public function getName() {
        return "Customize";
    }

    public function getUUID() {
        return "c4fe1b83-8f5a-4d1b-b912-172c608bf9e3";
    }    
    
    public function getPluginMenu(){
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Customize/pluginMenu.html';
        return file_get_contents($filename);
    }

}
