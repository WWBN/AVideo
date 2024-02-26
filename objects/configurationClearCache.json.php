<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->clearCache = false;
$obj->deleteALLCache = false;
$obj->deleteAllSessionCache = false;
$_SESSION['user']['sessionCache']['getAllCategoriesClearCache'] = 1;

if (!Permissions::canClearCache() || !empty($_REQUEST['sessionOnly'])) {
    $obj->deleteAllSessionCache = ObjectYPT::deleteAllSessionCache();
} else {
    if (!empty($_REQUEST['FirstPage'])) {
        $obj->firstPageCache = clearCache(true);
    } else {
        $obj->clearCache = clearCache();
        $obj->deleteALLCache = ObjectYPT::deleteALLCache();
    }
}
$obj->error = false;
die(json_encode($obj));
