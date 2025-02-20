<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
if (!User::isAdmin()) {
    gotToLoginAndComeBackHere(__("You can not manage plugins"));
    exit;
}
$_page = new Page(array('Plugins'));
$_page->setIncludeInHead(array('view/managerPlugins_head.php'));
$_page->setIncludeInBody('view/managerPlugins_body.php');
$_page->print();
?>
