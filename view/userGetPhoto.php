<?php

require_once '../videos/configuration.php';

$photo = User::getPhoto($_REQUEST['users_id']);

header("Location: {$photo}");
exit;