<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}
$obj = new PlayList($_POST['playlist_id']);
if(User::getId() != $obj->getUsers_id()){
    die('{"error":"'.__("Permission denied").'"}');
}

$count = 1;
foreach ($_POST['list'] as $key => $value) {
    error_log("SORT id: $value pos: $count");
    $result = $obj->addVideo($value, true, $count++);
}

echo '{"status":"'.$result.'"}';
