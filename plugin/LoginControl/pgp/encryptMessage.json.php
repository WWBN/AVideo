<?php
require_once '../../../videos/configuration.php';
require_once '../../../plugin/LoginControl/pgp/functions.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->textEncrypted = '';

// Security: require authentication to prevent unauthenticated abuse of this endpoint.
if (!User::isLogged()) {
    $obj->msg = 'Authentication required';
    die(json_encode($obj));
}

$encMessage = encryptMessage(@$_REQUEST['textToEncrypt'], @$_REQUEST['publicKeyToEncryptMsg']);
if (!empty($encMessage["encryptedMessage"])) {
    $obj->error = false;
    $obj->textEncrypted = $encMessage["encryptedMessage"];
}

die(json_encode($obj));
