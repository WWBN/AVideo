<?php
require_once 'captcha.php';
require_once 'configuration.php';
$config = new Configuration();
$valid = Captcha::validation($_POST['captcha']);
$obj = new stdClass();
if ($valid) {
    
    $msg = "<b>Name:</b> {$_POST['first_name']}<br> <b>Email:</b> {$_POST['email']}<br><br>";
    
    require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';

    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    setSiteSendMessage($mail);
    //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    //var_dump($mail->SMTPAuth, $mail);
    //Set who the message is to be sent from
    $mail->AddReplyTo($_POST['email'], $_POST['first_name']);
    $mail->setFrom($_POST['email'], $_POST['first_name']);
    //Set who the message is to be sent to
    $mail->addAddress($config->getContactEmail());
    //Set the subject line
    $mail->Subject = 'Message From Site '.$config->getWebSiteTitle(). " ({$_POST['first_name']})";
    $mail->msgHTML($msg.$_POST['comment']);

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
