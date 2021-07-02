<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';

class Scheduler extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Scheduler Plugin";
        $desc .= "<br>Crontab every 1 minute<br><code>* * * * * php {$global['systemRootPath']}plugin/Scheduler/run.php</code>";
        //$desc .= $this->isReadyLabel(array('YPTWallet'));
        return $desc;
    }

    public function getName() {
        return "Scheduler";
    }

    public function getUUID() {
        return "Scheduler-5ee8405eaaa16";
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
        return '<a href="plugin/Scheduler/View/editor.php" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</a>';
    }
    
    static public function run($scheduler_commands_id){
        global $_executeSchelude;
        if(!isset($_executeSchelude)){
            $_executeSchelude = array();
        }
        $e = new Scheduler_commands($scheduler_commands_id);
        $callBackURL = $e->getCallbackURL();
        if(!isValidURL($callBackURL)){
            return false;
        }
        if(empty($_executeSchelude[$callBackURL])){
            $_executeSchelude[$callBackURL] = url_get_contents($callBackURL);
        }
        if(!empty($_executeSchelude[$callBackURL])){
            return $e->setExecuted($_executeSchelude[$callBackURL]);
        }
        return false;
    }

    
    static public function add($date_to_execute, $callbackURL){
        _error_log("Scheduler::add [$date_to_execute] [$callbackURL]");
        if(empty($date_to_execute)){
            _error_log("Scheduler::add ERROR date_to_execute is empty");
            return false;
        }
        if(empty($callbackURL)){
            _error_log("Scheduler::add ERROR callbackURL is empty");
            return false;
        }
        $e = new Scheduler_commands(0);
        $e->setDate_to_execute($date_to_execute);
        $e->setCallbackURL($callbackURL);
        return $e->save();
    }

}
