<?php

//streamer config
require_once dirname(__FILE__) . '/../videos/configuration.php';

_error_log('sendSiteEmailAsync: Start');
if (!isCommandLineInterface()) {
    _error_log('sendSiteEmailAsync: ERROR: command line only');
    return die('Command Line only');
}

$tmpFile = @$argv[1];
if(empty($tmpFile)){
    _error_log('sendSiteEmailAsync: ERROR: empty argument');
    die('sendSiteEmailAsync empty argument');
}
if(!file_exists($tmpFile)){
    _error_log('sendSiteEmailAsync: ERROR: file do not exists '.$tmpFile);
    die('sendSiteEmailAsync file do not exists '.$tmpFile);
}
$json = _json_decode(file_get_contents($tmpFile));
unlink($tmpFile);
if(empty($json)){
    _error_log('sendSiteEmailAsync: ERROR JSON invalid');
    die('sendSiteEmailAsync JSON invalid');
}
$to = ($json->to);
$subject = ($json->subject);
$message = ($json->message);

sendSiteEmail($to, $subject, $message);
_error_log('sendSiteEmailAsync: Complete');
