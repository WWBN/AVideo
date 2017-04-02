<?php
header('Content-Type: application/json');
require_once 'user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
$user = new User($_POST['id']);
echo '{"status":"'.$user->delete().'"}';