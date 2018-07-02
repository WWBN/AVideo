<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class CloneSite extends PluginAbstract {
   
    public function getDescription() {
        $txt = "Clone and Backup YouPHPTube Sites";
        $help = "<br><small><a href='https://github.com/DanielnetoDotCom/YouPHPTube/wiki/Clone-Site-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "CloneSite";
    }

    public function getUUID() {
        return "c0731de9-b4f7-4462-bda6-458b0736593d";
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CloneSite/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getEmptyDataObject() {
        $obj = new stdClass();
        $obj->cloneSiteURL = "";
        $obj->myKey = md5(uniqid());
        return $obj;
    }

}
