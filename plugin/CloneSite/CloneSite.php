<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/functions.php';

class CloneSite extends PluginAbstract {
   

    public function getTags() {
        return array(
            PluginTags::$SECURITY,
            PluginTags::$FREE
        );
    }
    public function getDescription() {
        global $global;
        $obj = $this->getDataObject();
        $txt = "Clone and Backup AVideo Sites";
        $txt .= "<br>Crontab every day at 1am:<br><code>0 1 * * * php {$global['systemRootPath']}plugin/CloneSite/cloneClient.json.php {$obj->myKey}</code>";
        if(!isRsync()){
            $txt .= "<div class='alert alert-danger'>To use rsync feature you must install it <code>sudo apt-get install rsync</code></div>";
        }
        if(!isSshpass()){
            $txt .= "<div class='alert alert-danger'>To use rsync feature you must install sshpass <code>sudo apt-get install sshpass</code></div>";
        }
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Clone-Site-Plugin' target='__blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName() {
        return "CloneSite";
    }

    public function getUUID() {
        return "c0731de9-b4f7-4462-bda6-458b0736593d";
    }

    public function getPluginVersion() {
        return "1.1";   
    }

    public function getPluginMenu() {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/CloneSite/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        $obj->cloneSiteURL = "";
        $obj->cloneSiteSSHIP = "";
        $obj->cloneSiteSSHUser = "";
        $obj->cloneSiteSSHPort = "22";
        $o = new stdClass();
        $o->type = "encrypted";
        $o->value = "";        
        $obj->cloneSiteSSHPassword = $o;
        $obj->useRsync = true;
        $obj->MaintenanceMode = false;
        $obj->myKey = md5($global['systemRootPath'].$global['salt']);
        return $obj;
    }
    
    
    public function getStart() {
        $obj = $this->getDataObject();
        if($obj->MaintenanceMode){
            $m = AVideoPlugin::loadPlugin("MaintenanceMode");
            $m->getStart();
        }
    }

    public function getFooterCode() {
        $obj = $this->getDataObject();
        if($obj->MaintenanceMode){
            $m = AVideoPlugin::loadPlugin("MaintenanceMode");
            $m->getFooterCode();
        }
    }

}
