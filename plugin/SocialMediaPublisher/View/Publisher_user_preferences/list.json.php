<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_user_preferences.php';
header('Content-Type: application/json');

if(!User::isLogged()){
    forbiddenPage('Must login');
}

$rows = Publisher_user_preferences::getAllFromUsersId(User::getId());
$total = Publisher_user_preferences::getTotal();

$response = array(
    'data' => $rows,
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>