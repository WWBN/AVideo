<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugins"));
    exit;
}
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->message = "";
if (empty($_POST['menuItemId'])) {
    $obj->message = "Menu Item ID can not be empty";
} else {
    $menu = new MenuItem($_POST['menuItemId']);
    $obj->error = $menu->delete();
}

echo json_encode($obj);
?>