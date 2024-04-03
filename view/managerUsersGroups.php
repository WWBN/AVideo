<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!Permissions::canAdminUserGroups()) {
    forbiddenPage(__("You can not manage do this"));
}
$_page = new Page(array('User Groups'));
$_page->setIncludeInHead(array('view/managerUsersGroups_head.php'));
include $global['systemRootPath'] . 'view/managerUsersGroups_body.php';
$_page->print();
?>