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

$menu = new Menu(@$_POST['menuId']);
$menu->setMenuName($_POST['menuName']);
//$menu->setCategories_id(@$_POST['categories_id']);
$menu->setMenu_order(@$_POST['menu_order']);
//$menu->setPosition(@$_POST['position']);
$menu->setStatus(@$_POST['status']);
$menu->setType(@$_POST['type']);
//$menu->setUsers_groups_id(@$_POST['users_groups_id']);
$menu->setIcon(@$_POST['icon']);

$obj->error = $menu->save();

echo json_encode($obj); ?>