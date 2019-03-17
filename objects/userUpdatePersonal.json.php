<?php
header('Content-Type: application/json');
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

if(!User::isLogged()){
    die("Is not logged");
}

require_once $global['systemRootPath'] . 'objects/user.php';
$user = new User(0);
$user->loadSelfUser();
$user->setFirst_name($_POST['first_name']);
$user->setLast_name($_POST['last_name']);
$user->setAddress($_POST['address']);
$user->setZip_code($_POST['zip_code']);
$user->setCountry($_POST['country']);
$user->setRegion($_POST['region']);
$user->setCity($_POST['city']);
$fileData = base64DataToImage($_POST['imgBase64']);

User::saveDocumentImage($fileData, $user->getBdId());

echo '{"status":"'.$user->save().'"}';
User::updateSessionInfo();
