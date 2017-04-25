<?php
require_once 'like.php';
require_once $global['systemRootPath'] . 'objects/user.php';
header('Content-Type: application/json');
$like = new Like($_GET['like'], $_POST['videos_id']);
echo json_encode(Like::getLikes($_POST['videos_id']));