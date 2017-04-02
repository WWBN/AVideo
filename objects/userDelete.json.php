<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
$user = new User($_POST['id']);
echo '{"status":"'.$user->delete().'"}';