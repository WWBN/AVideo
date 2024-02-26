<?php
//streamer config
require_once dirname(__FILE__) . '/../videos/configuration.php';
ob_end_flush();
_error_log('sendSiteEmailAsync: Start');
if (!isCommandLineInterface()) {
    _error_log('sendSiteEmailAsync: ERROR: command line only');
    return die('Command Line only');
}else{
    _error_log('sendSiteEmailAsync: command line detected');
}

$tmpFile = @$argv[1];
if (empty($tmpFile)) {
    _error_log('sendSiteEmailAsync: ERROR: empty argument');
    die('sendSiteEmailAsync empty argument');
}else{
    _error_log('sendSiteEmailAsync: argument '.$tmpFile);
}
if (!file_exists($tmpFile)) {
    _error_log('sendSiteEmailAsync: ERROR: file do not exists '.$tmpFile);
    die('sendSiteEmailAsync file do not exists '.$tmpFile);
}else{
    _error_log('sendSiteEmailAsync: file do exists '.$tmpFile);
}
$json = json_decode(file_get_contents($tmpFile));
//unlink($tmpFile);
if (empty($json)) {
    _error_log('sendSiteEmailAsync: ERROR JSON invalid');
    die('sendSiteEmailAsync JSON invalid');
}else{
    _error_log('sendSiteEmailAsync: JSON is valid');
}
$to = ($json->to);
$subject = ($json->subject);
$message = ($json->message);

_error_log('sendSiteEmailAsync: JSON decode');
sendSiteEmail($to, $subject, $message);
_error_log('sendSiteEmailAsync: Complete');
