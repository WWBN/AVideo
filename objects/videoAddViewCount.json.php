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

if(empty($_SESSION['addViewCount'])){
    $_SESSION['addViewCount'] = array();
}

if(!in_array($_POST['id'],$_SESSION['addViewCount'])){
    $resp = $obj->addView();
    $_SESSION['addViewCount'][] = $_POST['id'];
}else{
    $resp = 0;
}
echo '{"status":"'.!empty($resp).'"}';
