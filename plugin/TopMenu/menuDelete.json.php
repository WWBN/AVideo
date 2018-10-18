<?php

require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugins"));
    exit;
}
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->error = true;
$obj->message = "";
if (empty($_POST['menuId'])) {
    $obj->message = "Menu ID can not be empty";
} else {
    $menu = new Menu($_POST['menuId']);
    $obj->error = $menu->delete();
}

echo json_encode($obj);
?>