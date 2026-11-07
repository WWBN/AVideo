<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::canUpload()) {
    forbiddenPage('You can not notify');
}
forbidIfIsUntrustedRequest('notifySubscribers');
$user_id = User::getId();
// if admin bring all subscribers
if (User::isAdmin()) {
    $user_id = '';
}

require_once 'subscribe.php';
setRowCount(10000);
header('Content-Type: application/json');
$Subscribes = Subscribe::getAllSubscribes($user_id);

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

$to = array();
foreach ($Subscribes as $value) {
    $to[] = $value["email"];
}

$subject = 'Message From Site ' . $config->getWebSiteTitle();
$message = (string)($_POST['message'] ?? '');
// No arbitrary HTML in branded emails: only the TinyMCE formatting is kept.
require_once $global['systemRootPath'] . 'objects/emailMessageHTML.php';
$message = purifyEmailMessageHTML($message, getCacheDir());

$resp = sendSiteEmail($to, $subject, $message);

$obj->error = empty($resp);

$json = json_encode($obj);

_error_log('NotifySubscribers emails: '.$json);

echo $json;
