<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Audit/Objects/AuditTable.php';

class Audit extends PluginAbstract {

    public function getDescription() {
        return "Save all insert, update and delete queries for audit";
    }

    public function getName() {
        return "Audit";
    }

    public function getUUID() {
        return "26570956-dc62-46e3-ace9-86c6e8f9c81b";
    }  

    public function getPluginVersion() {
        return "1.0";   
    }
        
    public function getPluginMenu(){
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/Audit/pluginMenu.html';
        return file_get_contents($filename);
    }
    
    function exec($method, $class, $statement, $formats, $values, $users_id) {
        $audit = new AuditTable(0);
        return $audit->audit($method, $class, $statement, $formats, $values, $users_id);
    }

}
