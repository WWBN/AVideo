<?php
require_once 'captcha.php';
$valid = Captcha::validation($_POST['captcha']);
$obj = new stdClass();
if ($valid) {
    require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';

    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    // Set PHPMailer to use the sendmail transport
    $mail->isSendmail();
    //Set who the message is to be sent from
    $mail->setFrom($_POST['email'], $_POST['first_name']);
    //Set who the message is to be sent to
    $mail->addAddress($global['contactEmail']);
    //Set the subject line
    $mail->Subject = 'Message From Site '.$global['webSiteTitle']. " ({$_POST['first_name']})";
    $mail->msgHTML($_POST['comment']);

    //send the message, check for errors
    if (!$mail->send()) {
        $obj->error = __("Message could not be sent")." ". $mail->ErrorInfo;
    } else {
        $obj->success = __("Message sent");
    }
} else {
    $obj->error = __("Your code is not valid");
}

header('Content-Type: application/json');
echo json_encode($obj);
