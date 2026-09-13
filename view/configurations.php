<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'admin/functions.php';

if (!User::isAdmin()) {
    forbiddenPage('');
}

// Reuse the admin diagnostic component without delaying the configuration form.
if (!empty($_GET['healthCheck'])) {
    adminSecurityCheck(true);
    include $global['systemRootPath'] . 'admin/health_check.php';
    exit;
}

$_page = new Page(array('Configuration'));
$_page->setIncludeInHead(array('view/configurations_head.php'));
include $global['systemRootPath'] . 'view/configurations_body.php';
$_page->print();
?>