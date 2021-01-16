<?php

//header("Content-type: text/xml");
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!Permissions::canGenerateSiteMap()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
$sitemap = siteMap();

if(empty($sitemap)){
    $obj->msg = "Sitemap content is empty";
    die(json_encode($obj));
}


$name = "sitemap.xml";
ObjectYPT::setCache($name, $sitemap);

$obj->error = false;
die(json_encode($obj));
