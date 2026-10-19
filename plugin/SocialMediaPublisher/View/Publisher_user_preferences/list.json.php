<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/SocialMediaPublisher/Objects/Publisher_user_preferences.php';
header('Content-Type: application/json');

if(!User::isLogged()){
    forbiddenPage('Must login');
}

$rows = Publisher_user_preferences::getAllFromUsersId(User::getId());
if (!is_array($rows)) {
    $rows = array();
}
foreach ($rows as $key => $row) {
    $expiresAt = YouTubePublisher::getTokenExpiresAt(@$row['json']);
    // "expired" alone is not a problem when the connection can renew itself before publishing
    $rows[$key]['connectionStatus'] = 'connected';
    if (!empty($row['accessTokenExpired'])) {
        $rows[$key]['connectionStatus'] = !empty($row['canRefreshAccessToken']) ? 'renewable' : 'reconnect';
    }
    $rows[$key]['isYouTube'] = YouTubePublisher::isYouTubeProvider($row['name']);
    $rows[$key]['expires_at'] = $expiresAt;
    // never send the tokens to the browser
    $rows[$key] = SocialMediaPublisher::getPublicConnectionRow($rows[$key]);
}
$total = Publisher_user_preferences::getTotalFromUsersId(User::getId());

$response = array(
    'data' => array_values($rows),
    'draw' => intval(@$_REQUEST['draw']),
    'recordsTotal' => $total,
    'recordsFiltered' => $total,
);
echo _json_encode($response);
?>
