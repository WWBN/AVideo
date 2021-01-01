<?php
$phpmailerDir = $global['systemRootPath'] . 'objects/phpmailer/';
if(!is_dir($phpmailerDir)){
    $phpmailerDir = $global['systemRootPath'] . 'objects/PHPMailer/';
}
require_once $phpmailerDir . 'phpmailer/src/PHPMailer.php';
require_once $phpmailerDir . 'phpmailer/src/SMTP.php';
require_once $phpmailerDir . 'phpmailer/src/Exception.php';