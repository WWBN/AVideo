<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $global['systemRootPath'] . 'objects/plugin.php';

class MeetAPI 
{
    public function getAPISecret()
    {
        global $global, $config;
        $pluginClass = new Plugin();
        $plugin = $pluginClass->getPluginByName("API");
        $object_data = json_decode($plugin['object_data']);
        if ($plugin['object_data'] == "" || !property_exists($object_data, "APISecret")) {
            return md5($global['systemRootPath']);
        }
        return $object_data->APISecret;
    }
    
    public function showMeetingPage()
    {   
        global $global, $config;
        $end_meet_redirect = $_REQUEST['end_meet_redirect'];
        ob_start();
        include $global['systemRootPath'] . 'plugin/Meet/api/meet.php';
        $file = ob_get_clean();
        return $file;
    }

    public function loadLiveMeetIframe()
    {   
        global $global, $config;
        $domain_ = $_REQUEST['domain'];
        $logo_ = $_REQUEST['logo'];
        $meet_schedule_id = $_REQUEST['meet_schedule_id'];
        $end_meet_redirect = $_REQUEST['end_meet_redirect'];
        if (isset($_REQUEST['meet_password'])) {
            $_POST['meet_password'] = $_REQUEST['meet_password'];
        }
        $userCredentials = User::loginFromRequestToGet();
        ob_start();
        include $global['systemRootPath'] . 'plugin/Meet/api/iframe.php';
        $file = ob_get_clean();
        return $file;
    }

    public function showChangeServerPage()
    {
        global $global, $config;
        $domain_ = $_REQUEST['domain'];
        ob_start();
        include $global['systemRootPath'] . 'plugin/Meet/api/checkServers.php';
        $file = ob_get_clean();
        return $file;
    }

    public function createMeet()
    {
        global $global, $config;
        if (!isset($_REQUEST['apiKey']) || $_REQUEST['apiKey'] != $this->getAPISecret()) {
            return array("error" => "true", "message" => "API KEY is required.");
        }
        if (isset($_REQUEST['userGroups'])) {
            $_REQUEST['userGroups'] = json_decode($_REQUEST['userGroups']);
        }
        ob_start();
        include $global['systemRootPath'] . 'plugin/Meet/saveMeet.json.php';
        $save = ob_get_clean();
        if ($save['error']) {
            return array("error" => "true", "message" => "Ops! Something went wrong, failed to save meet.");
        } else {
            return array("error" => "false", "data" => $save);
        }
        
    }

    public function getUserGroups()
    {
        global $global, $config;
        if (!isset($_REQUEST['apiKey']) || $_REQUEST['apiKey'] != $this->getAPISecret()) {
            return array("error" => "true", "message" => "API KEY is required.");
        }
        require_once $global['systemRootPath'] . 'objects/user.php';
        require_once $global['systemRootPath'] . 'objects/userGroups.php';
        return array("error" => "false", "data" => UserGroups::getAllUsersGroups());
    }

    public function getLiveMeet()
    {
        global $global, $config;
        if (!isset($_REQUEST['apiKey']) || $_REQUEST['apiKey'] != $this->getAPISecret()) {
            return array("error" => "true", "message" => "API KEY is required.");
        }
        require_once $global['systemRootPath'] . 'objects/user.php';
        require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';

        $meet_sched = new Meet_schedule();
        $data = $meet_sched->getMeetByID($_REQUEST['meet_id']);

        return array("error" => "false", "data" => $data);
    }

    public function deleteLiveMeet()
    {
        global $global, $config;
        if (!isset($_REQUEST['apiKey']) || $_REQUEST['apiKey'] != $this->getAPISecret()) {
            return array("error" => "true", "message" => "API KEY is required.");
        }
        require_once $global['systemRootPath'] . 'objects/user.php';
        require_once $global['systemRootPath'] . 'plugin/Meet/Objects/Meet_schedule.php';
        
        $meet_sched = new Meet_schedule();
        if ($meet_sched->getMeetByID($_REQUEST['meet_id']) == null) {
            $data = false;
        } else {
            $data = $meet_sched->deleteMeetByID($_REQUEST['meet_id']);
        }

        return array("error" => "false", "data" => $data);
    }
}
