<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
if (!Category::canCreateCategory()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage categories"));
    exit;
}
$_page = new Page(array('Categories'));
$_page->setIncludeInHead(array('view/managerCategories_head.php'));
include $global['systemRootPath'] . 'view/managerCategories_body.php';
$_page->print();
 ?>

