<?php

require_once  '../../../plugin/LoginControl/pgp/functions.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->textEncrypted = '';

$encMessage = encryptMessage(@$_REQUEST['textToEncrypt'], @$_REQUEST['publicKeyToEncryptMsg']);
if(!empty($encMessage["encryptedMessage"])){
    $obj->error = false;
    $obj->textEncrypted = $encMessage["encryptedMessage"];
}

die(json_encode($obj));