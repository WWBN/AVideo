<?php

//streamer config
require_once '../videos/configuration.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

$tmpFile = @$argv[1];
if(empty($tmpFile)){
    die('sendSiteEmailAsync empty argument');
}
if(!file_exists($tmpFile)){
    die('sendSiteEmailAsync file do not exists '.$tmpFile);
}
$json = _json_decode(file_get_contents($tmpFile));
unlink($tmpFile);
if(empty($json)){
    die('sendSiteEmailAsync JSON invalid');
}
$to = ($json->to);
$subject = ($json->subject);
$message = ($json->message);

sendSiteEmail($to, $subject, $message);
