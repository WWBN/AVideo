<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
$config->setContactEmail($_POST['contactEmail']);
$config->setLanguage($_POST['language']);
$config->setVideo_resolution($_POST['video_resolution']);
$config->setWebSiteTitle($_POST['webSiteTitle']);
echo '{"status":"'.$config->save().'"}';