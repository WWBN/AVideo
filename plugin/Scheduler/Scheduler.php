<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

require_once $global['systemRootPath'] . 'plugin/Scheduler/Objects/Scheduler_commands.php';

class Scheduler extends PluginAbstract {

    public function getDescription() {
        global $global;
        $desc = "Scheduler Plugin";
        if (!_isSchedulerPresentOnCrontab()) {
            $desc = "<strong onclick='tooglePluginDescription(this);'>";
            $desc .= "To use the Scheduler Plugin, you MUST add it on your crontab";
            $desc .= "</strong>";
            $desc .= "<br>Open a terminal and type <code>crontab -e</code> than add a crontab for every 1 minute<br><code>* * * * * php {$global['systemRootPath']}plugin/Scheduler/run.php</code>";
        }
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
        return "3.0";
    }

    public function updateScript() {
        global $global;
        if (AVideoPlugin::compareVersion($this->getName(), "2.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Scheduler/install/updateV2.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
        if (AVideoPlugin::compareVersion($this->getName(), "3.0") < 0) {
            $sqls = file_get_contents($global['systemRootPath'] . 'plugin/Scheduler/install/updateV3.0.sql');
            $sqlParts = explode(";", $sqls);
            foreach ($sqlParts as $value) {
                sqlDal::writeSql(trim($value));
            }
        }
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
        $btn = '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/Scheduler/View/editor.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fa fa-edit"></i> Edit</button>';
        $btn .= '<button onclick="avideoModalIframeLarge(webSiteRootURL+\'plugin/Scheduler/run.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-terminal"></i> Run now</button>';
        return $btn;
    }

    static public function run($scheduler_commands_id) {
        global $_executeSchelude,$global;
        if (!isset($_executeSchelude)) {
            $_executeSchelude = array();
        }
        $e = new Scheduler_commands($scheduler_commands_id);
        $callBackURL = $e->getCallbackURL();
        $callBackURL = str_replace('{webSiteRootURL}', $global['webSiteRootURL'], $callBackURL);
        if (!isValidURL($callBackURL)) {
            return false;
        }
        if (empty($_executeSchelude[$callBackURL])) {
            $callBackURL = addQueryStringParameter($callBackURL, 'token', getToken(60));
            $callBackURL = addQueryStringParameter($callBackURL, 'scheduler_commands_id', $scheduler_commands_id);
            _error_log("Scheduler::run getting callback URL {$callBackURL}");
            $_executeSchelude[$callBackURL] = url_get_contents($callBackURL, '', 30);
            _error_log("Scheduler::run got callback " . json_encode($_executeSchelude[$callBackURL]));
            $json = _json_decode($_executeSchelude[$callBackURL]);
            if(is_object($json) && !empty($json->error)){
                _error_log("Scheduler::run callback ERROR " . json_encode($json));
                return false;
            }
        }
        if (!empty($_executeSchelude[$callBackURL])) {
            return $e->setExecuted($_executeSchelude[$callBackURL]);
        }
        return false;
    }

    static public function add($date_to_execute, $callbackURL, $parameters = '', $type = '') {
        _error_log("Scheduler::add [$date_to_execute] [$callbackURL]");
        if (empty($date_to_execute)) {
            _error_log("Scheduler::add ERROR date_to_execute is empty");
            return false;
        }
        
        $date_to_execute_time = _strtotime($date_to_execute);
        
        if ($date_to_execute_time <= time()) {
            _error_log("Scheduler::add ERROR date_to_execute must be greater than now [{$date_to_execute}] ".date('Y/m/d H:i:s', $date_to_execute_time).' '.date('Y/m/d H:i:s'));
            return false;
        }

        if (empty($callbackURL)) {
            _error_log("Scheduler::add ERROR callbackURL is empty");
            return false;
        }
        $e = new Scheduler_commands(0);
        $e->setDate_to_execute($date_to_execute);
        $e->setCallbackURL($callbackURL);        
        if (!empty($parameters)) {
            $e->setParameters($parameters);
        }
        if (!empty($type)) {
            $e->setType($type);
        }
        return $e->save();
    }

    static public function addSendEmail($date_to_execute, $emailTo, $emailSubject, $emailEmailBody, $emailFrom = '', $emailFromName = '', $type = '') {
        global $global;
        $parameters = array(
            'emailSubject' => $emailSubject,
            'emailEmailBody' => $emailEmailBody,
            'emailTo' => $emailTo,
            'emailFrom' => $emailFrom,
            'emailFromName' => $emailFromName,
        );
        //var_dump($parameters);exit;
        $url = "{webSiteRootURL}plugin/Scheduler/sendEmail.json.php";

        if (empty($type)) {
            $type = 'SendEmail';
        }

        $scheduler_commands_id = Scheduler::add($date_to_execute, $url, $parameters, $type);
        return $scheduler_commands_id;
    }

    static public function getReminderOptions($destinationURL, $selectedEarlierOptions = array(), $earlierOptions = array(
                '10 minutes earlier' => 10,
                '30 minutes earlier' => 30,
                '1 hour earlier' => 60,
                '2 hours earlier' => 120,
                '1 day earlier' => 1440,
                '2 days earlier' => 2880,
                '1 week earlier' => 10080
            )
    ) {
        global $global;
        $varsArray = array('destinationURL' => $destinationURL, 'earlierOptions' => $earlierOptions, 'selectedEarlierOptions' => $selectedEarlierOptions);
        $filePath = "{$global['systemRootPath']}plugin/Scheduler/reminderOptions.php";
        return getIncludeFileContent($filePath, $varsArray);
    }

}
