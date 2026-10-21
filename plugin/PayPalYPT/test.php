<?php
// Former developer script (it listed and created webhooks without login). The configuration check replaces it.
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage('Permission denied', true);
}
header("Location: {$global['webSiteRootURL']}plugin/PayPalYPT/configuration.php");
