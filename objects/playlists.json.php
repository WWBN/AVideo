<?php
require_once './playlist.php';
header('Content-Type: application/json');
$row = PlayList::getAllFromUser(User::getId(), false);
echo json_encode($row);
