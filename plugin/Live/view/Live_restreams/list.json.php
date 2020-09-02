<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/Live_restreams.php';
header('Content-Type: application/json');

if(!User::canStream()){
    die('{"data": []}');
}

if(empty($_GET['users_id'])){
    if(!User::isAdmin()){
        $_GET['users_id'] = User::getId();
    }
}

if(empty($_GET['users_id'])){
    $rows = Live_restreams::getAll();
}else{
    $rows = Live_restreams::getAllFromUser($_GET['users_id'], "");
}


?>
{"data": <?php echo json_encode($rows); ?>}