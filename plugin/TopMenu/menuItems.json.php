<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';
header('Content-Type: application/json');

if(empty($_POST['sort'])){
    $_POST['sort'] = array('item_order'=>"ASC");
}

$menu = MenuItem::getAllFromMenu($_POST['menuId']);
?>
{"data": <?php echo json_encode($menu); ?>}