<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

// gettig the mobile submited value
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
if(!empty($input) && empty($_POST)){
    foreach ($input as $key => $value) {
        $_POST[$key]=$value;
    }
}
if(!empty($_POST['user']) && !empty($_POST['pass'])){
    $user = new User(0, $_POST['user'], $_POST['pass']);
    $user->login(false, true);
}


$obj = new stdClass();
if(YouPHPTubePlugin::loadPluginIfEnabled("Live")){
    $liveStats = file_get_contents("{$global['webSiteRootURL']}plugin/Live/stats.json.php");
    $obj->live = json_decode($liveStats);
}

echo json_encode($obj);