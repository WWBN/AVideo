<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

header('Content-Type: application/json');
if (empty($_POST['email'])) {
    if(!empty($_REQUEST['users_groups_id'])){
        $users = User::getAllUsersFromUsergroup($_REQUEST['users_groups_id'],false, array('name', 'email', 'user', 'channelName', 'about'), "a");
    }else{
        $users = User::getAllUsers(false, array('name', 'email', 'user', 'channelName', 'about'), "a");
    }
} else {
    $users[0]["email"] = $_POST['email'];
}
require_once $global['systemRootPath'] . 'objects/PHPMailer/src/PHPMailer.php';
require_once $global['systemRootPath'] . 'objects/PHPMailer/src/SMTP.php';
require_once $global['systemRootPath'] . 'objects/PHPMailer/src/Exception.php';
// send 100 emails at a time
$mailsLimit = 100;

$obj = new stdClass();
$obj->error = false;
$obj->msg = array();
//Create a new PHPMailer instance
$mail = new PHPMailer\PHPMailer\PHPMailer;
setSiteSendMessage($mail);
//Set who the message is to be sent from
$mail->setFrom($config->getContactEmail());
$mail->Subject = 'Message From Site ' . $config->getWebSiteTitle();
$mail->msgHTML($_POST['message']);
$count = 0;
$currentCount = 0;
foreach ($users as $value) {
    if ($count % $mailsLimit === 0) {
        if ($count !== 0) {
            if (!$mail->send()) {
                $obj->error = true;
                $obj->msg[] = __("Message could not be sent") . " " . $mail->ErrorInfo;
            }
            $mail->ClearAddresses();
            $mail->ClearCCs();
            $mail->ClearBCCs();
            $currentCount = 0;
        }
    }
    $mail->addBCC($value["email"]);
    $count++;
    $currentCount++;
}
if ($currentCount && !$mail->send()) {
    $obj->error = true;
    $obj->msg[] = __("Message could not be sent") . " " . $mail->ErrorInfo;
}
$obj->count = $count;
echo json_encode($obj);
