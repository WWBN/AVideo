<?php
header('Content-Type: application/json');
require_once 'user.php';
if (!User::isLogged()) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'comment.php';
$obj = new Comment($_POST['comment'], $_POST['video']);
echo '{"status":"'.$obj->save().'"}';