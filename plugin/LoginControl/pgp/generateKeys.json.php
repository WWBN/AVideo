<?php
require_once '../../../videos/configuration.php';
require_once '../../../plugin/LoginControl/pgp/functions.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->public = '';
$obj->private = '';

// Security: require authentication — key generation is CPU-intensive and must not be exposed anonymously.
if (!User::isLogged()) {
    $obj->msg = 'Authentication required';
    die(json_encode($obj));
}


$pass = @$_REQUEST['keyPassword'];
$name = @$_REQUEST['keyName'];
$email = @$_REQUEST['keyEmail'];
$UserIDPacket = "{$name} <{$email}>";

$keys = createKeys($UserIDPacket, $pass);

if (!empty($keys['public']) && !empty($keys['private'])) {
    $obj->error = false;
    $obj->public = $keys['public'];
    $obj->private = $keys['private'];
}
die(json_encode($obj));
