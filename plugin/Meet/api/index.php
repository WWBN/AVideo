<?php
header('Content-Type: application/json');
if (isset($_REQUEST['domain'])) {
    header('Access-Control-Allow-Origin: '.$_REQUEST['domain']);
    header("Access-Control-Allow-Credentials", true);
} else {
    header('Access-Control-Allow-Origin: *');
}
// header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($global['systemRootPath'])) {
    $configFile = '../../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

// print_r(User::getId()); die();
require_once('API.php');
$meetAPI = new MeetAPI();

$parameters = array_merge($_GET, $_POST);

$_REQUEST['rememberme'] = "true";
// $user = new User(0, $parameters['user'], $parameters['pass']);
// $resp = $user->login(false, true);

if (!isset($parameters['apiName']) && empty($parameters['apiName'])) {
	die(json_encode(array("error"=>"true","message"=>"Error! Please provide apiName field.")));
}

$function = $parameters['apiName'];

// print_r($function);die();
if ($function == 'showMeetingPage') {
	die($meetAPI->$function($parameters));
} else if ($function == 'loadLiveMeetIframe') {
	die($meetAPI->$function($parameters));
} else if ($function == 'showChangeServerPage') {
	die($meetAPI->$function($parameters));
}
die(json_encode($meetAPI->$function($parameters)));

