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

foreach ($_POST['itens'] as $key => $value) {    
    $menu = new MenuItem($value);
    $menu->setItem_order($key+10);
    $obj->error = $menu->save();
}

echo json_encode($obj); ?>