<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!Permissions::canAdminUsers()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage users"));
    exit;
}
$_page = new Page(array('Users'));
$_page->setIncludeInHead(array('view/managerUsers_head.php'));
include $global['systemRootPath'] . 'view/managerUsers_body.php';
$_page->print();
?>