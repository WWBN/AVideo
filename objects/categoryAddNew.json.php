<?php
error_reporting(0);
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'category.php';
$obj = new Category(@$_POST['id']);
$obj->setName($_POST['name']);
$obj->setClean_name($_POST['clean_name']);
$obj->setDescription(nl2br ($_POST['description']));
$obj->setIconClass($_POST['iconClass']);
$obj->setNextVideoOrder($_POST['nextVideoOrder']);
$obj->setParentId($_POST['parentId']);
$obj->setType($_POST['type']);
echo '{"status":"'.$obj->save().'"}';
