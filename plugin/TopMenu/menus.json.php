<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugins"));
    exit;
}
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
header('Content-Type: application/json');

if(empty($_POST['sort'])){
    $_POST['sort'] = array('menu_order'=>"ASC");
}
$menu = Menu::getAll();
?>
{"data": <?php echo json_encode($menu); ?>}
