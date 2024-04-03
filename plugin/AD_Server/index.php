<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
    exit;
}
$_page = new Page(array('VAST'));
$_page->setIncludeInHead(array('plugin/AD_Server/index_head.php'));
$_page->setIncludeInBody(array('plugin/AD_Server/index_body.php'));
$_page->print();
?>