<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/captcha.php';
$config = new AVideoConf();
$valid = Captcha::validation(@$_POST['captcha']);
if(User::isAdmin()){
    $valid = true;
}
$obj = new stdClass();
$obj->error = '';
if ($valid) {
    // Sanitize user inputs to prevent HTML injection (email spoofing/phishing)
    $safeEmail = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $safeComment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
    // Convert newlines to <br> for proper display in email after sanitization
    $safeComment = nl2br($safeComment);
    $msg = "<b>Email:</b> {$safeEmail}<br><br>{$safeComment}";
    //Create a new PHPMailer instance
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
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
    if (!empty($_POST['contactForm'])) {
        $replyTo = $_POST['email'];
        $sendTo = $config->getContactEmail();
    }

    if (filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
        $mail->AddReplyTo($replyTo);
        $mail->setFrom($replyTo);
        //Set who the message is to be sent to
        $mail->addAddress($sendTo);
        //Set the subject line
        $safeFirstName = htmlspecialchars($_POST['first_name'], ENT_QUOTES, 'UTF-8');
        $mail->Subject = 'Message From Site ' . $config->getWebSiteTitle() . " ({$safeFirstName})";
        $mail->msgHTML($msg);

        _error_log("Send email now to {$sendTo}");
        //send the message, check for errors
        if (!$mail->send()) {
            $obj->error = __("Message could not be sent") . " (" . $mail->ErrorInfo.")";
        } else {
            $obj->success = __("Message sent");
        }
    } else {
        $obj->error = __("The email is invalid")." {$sendTo}";
    }
} else {
    $obj->error = __("Your code is not valid");
}
_error_log("sendEmail: ".$obj->error);
header('Content-Type: application/json');
echo json_encode($obj);
