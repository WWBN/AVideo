<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');

$_POST['current'] = 1;
$_REQUEST['rowCount'] = 10;

$response = array();

if(preg_match('/^@/', $_REQUEST['term'])){
    $_GET['searchPhrase'] = xss_esc(substr($_REQUEST['term'], 1));
    $ignoreAdmin = true;
    $users = User::getAllUsers($ignoreAdmin, ['name', 'email', 'user', 'channelName'], 'a');
    foreach ($users as $key => $value) {
        $response[] = array(
            'id'=>$value['id'],
            'value'=>$value['identification'], 
            'label'=>Video::getCreatorHTML($value['id'], '', true, true)
            );
    }
}


echo json_encode($response);
