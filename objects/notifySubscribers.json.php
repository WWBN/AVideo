<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::canUpload()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not notify"));
    exit;
}
$user_id = User::getId();
// if admin bring all subscribers
if(User::isAdmin()){
    $user_id = "";
}

require_once 'subscribe.php';
header('Content-Type: application/json');
$Subscribes = Subscribe::getAllSubscribes($user_id);
require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';

$obj = new stdClass();
//Create a new PHPMailer instance
$mail = new PHPMailer;
setSiteSendMessage($mail);
//Set who the message is to be sent from
$mail->setFrom($config->getContactEmail());
//Set who the message is to be sent to
//$mail->addAddress($config->getContactEmail());
foreach ($Subscribes as $value) {
    $mail->addBCC($value["email"]);
}
$obj->total = count($Subscribes);
//Set the subject line
$mail->Subject = 'Message From Site ' . $config->getWebSiteTitle();
$mail->msgHTML($_POST['message']);

//send the message, check for errors
if (!$mail->send()) {
    $obj->error = __("Message could not be sent") . " " . $mail->ErrorInfo;
} else {
    $obj->success = __("Message sent");
}

echo json_encode($obj);
