<?php
header('Content-Type: application/json');
if (empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
require_once 'video.php';
$obj = new Video("", "", $_POST['id']);
if(empty($obj)){
    die("Object not found");
}
$resp = $obj->addView();
echo '{"status":"'.!empty($resp).'"}';