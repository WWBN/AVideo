<?php
header('Content-Type: application/json');
require_once __DIR__.'/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/UserConnections/Objects/Users_connections.php';

if(!User::isLogged()){
    forbiddenPage('Must Login');
}

$response = new stdClass();
$response->draw = intval(@$_GET['draw']);
$response->data = UserConnections::getAllMyConnections();
$response->recordsTotal = count($response->data);
$response->recordsFiltered = count($response->data);

$chatIsEnabled = AVideoPlugin::isEnabledByName('Chat2');

foreach ($response->data as $key => $value) {
    $value['buttons'] = UserConnections::getConnectionButtons($value['friend_users_id']);
    $value['callButton'] =  getUserOnlineLabel($value['friend_users_id']);
    $value['channelLink'] =  User::getChannelLink($value['friend_users_id']);
    if($chatIsEnabled){
        $value['chatButton'] =  Chat2::getUserChatButton($value['friend_users_id']);
    }else{
        $value['chatButton'] =  'Chat available';
    }
    $response->data[$key] = $value;
}

echo json_encode($response);
