<?php
header('Content-Type: application/json');

if (!isset($global['systemRootPath'])) {
    error_log(__FILE__ . " line " . __LINE__);
    $configFile = __DIR__ . '/../../videos/configuration.php';
    error_log(__FILE__ . " line " . __LINE__);
    if (file_exists($configFile)) {
        error_log(__FILE__ . " line " . __LINE__);
        require_once $configFile;
    }
    error_log(__FILE__ . " line " . __LINE__);
}

error_log(__FILE__ . " line " . __LINE__ . ' REQUEST=' . json_encode($_REQUEST));
// Get all request headers
$headers = getallheaders();
$token = '';
// Check if the Authorization header exists
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    // Extract token after "Bearer "
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        _error_log(__FILE__ . " Token: Found");
    } else {
        _error_log(__FILE__ . " Invalid Authorization header format.");
    }
} else {
    _error_log(__FILE__ . " No Authorization header found.");
}

if (empty($token)) {
    forbiddenPage('Token not found');
}
error_log(__FILE__ . " line " . __LINE__);

$objM = AVideoPlugin::getObjectDataIfEnabled("Meet");

error_log(__FILE__ . " line " . __LINE__);
if (empty($objM)) {
    forbiddenPage('Plugin disabled');
}
error_log(__FILE__ . " line " . __LINE__);

if ($objM->secret != $token) {
    forbiddenPage('Token does not match');
}
error_log(__FILE__ . " line " . __LINE__);

if (empty($_FILES['upl'])) {
    forbiddenPage('videoFile not found');
}
error_log(__FILE__ . " line " . __LINE__);

$users_id = explode('-', $_FILES['upl']['name'])[0];

error_log(__FILE__ . " line " . __LINE__);
$userObject = new User($users_id);
$userObject->login(true, true);

$tmpFile = getTmpDir() . uniqid();

error_log(__FILE__ . " line " . __LINE__);
if (move_uploaded_file($_FILES['upl']['tmp_name'], $tmpFile)) {
    _error_log(__FILE__ . " including aVideoQueueEncoder filesize = " .  humanFileSize(filesize($tmpFile)));
    $_FILES['upl']['tmp_name'] = $tmpFile;
    _error_log(__FILE__ . " including aVideoQueueEncoder " .  json_encode($_FILES));
    require $global['systemRootPath'] . 'objects/aVideoQueueEncoder.json.php';
    _error_log(__FILE__ . " complete aVideoQueueEncoder ");
} else {
    _error_log(__FILE__ . " complete aVideoQueueEncoder ERROR on move file {$_FILES['upl']['tmp_name']} => $tmpFile");
}
