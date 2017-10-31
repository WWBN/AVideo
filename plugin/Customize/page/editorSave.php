<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin customize"));
    exit;
}
require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';

$ec = new ExtraConfig();
$ec->setAbout($_POST['about']);
$ec->setDescription($_POST['description']);
$ec->setFooter($_POST['footer']);
$obj = new stdClass();
$obj->save = $ec->save();

echo json_encode($obj);
?>