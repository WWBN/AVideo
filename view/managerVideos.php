<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user?redirectUri={$global['webSiteRootURL']}mvideos");
    exit;
}

if (!User::canUpload(true)) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage videos"));
    exit;
}

if (!empty($_GET['iframe'])) {
    $_GET['noNavbar'] = 1;
}

$_page = new Page(array('Videos'));
$_page->loadBasicCSSAndJS();
$_page->setIncludeInHead(array('view/managerVideos_head.php'));
include $global['systemRootPath'] . 'view/managerVideos_body.php';
$_page->print();
?>