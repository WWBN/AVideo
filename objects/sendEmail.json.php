<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/captcha.php';
$config = new Configuration();
$valid = Captcha::validation(@$_POST['captcha']);
$obj = new stdClass();
if ($valid) {

    $msg = "<b>Name:</b> {$_POST['first_name']}<br> <b>Email:</b> {$_POST['email']}<br><b>Website:</b> {$_POST['website']}<br><br>{$_POST['comment']}";

    require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';

    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    setSiteSendMessage($mail);
    //$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    //var_dump($mail->SMTPAuth, $mail);
    //Set who the message is to be sent from

    $replyTo = User::getEmail_();
    if (empty($replyTo)) {
        $replyTo = $config->getContactEmail();
    }

    $sendTo = $_POST['email'];
    
    // if it is from contact form send the message to the siteowner and the sender is the email on the form field
    if(!empty($_POST['contactForm'])){
        $replyTo = $_POST['email'];
        $sendTo = $config->getContactEmail();
    }
    
    if (filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
        $mail->AddReplyTo($replyTo);
        $mail->setFrom($replyTo);
        //Set who the message is to be sent to
        $mail->addAddress($sendTo);
        //Set the subject line
        $mail->Subject = 'Message From Site ' . $config->getWebSiteTitle() . " ({$_POST['first_name']})";
        $mail->msgHTML($msg);

        //send the message, check for errors
        if (!$mail->send()) {
            $obj->error = __("Message could not be sent") . " " . $mail->ErrorInfo;
        } else {
            $obj->success = __("Message sent");
        }
    } else {
        $obj->error = __("The email is invalid");
    }
} else {
    $obj->error = __("Your code is not valid");
}

header('Content-Type: application/json');
echo json_encode($obj);
