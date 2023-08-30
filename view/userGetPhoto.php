<?php
//$doNotConnectDatabaseIncludeConfig =
//require_once '../videos/configuration.php';

//$photo = User::getPhotoRelativePath($_REQUEST['users_id']);

if (empty($_REQUEST['users_id'])) {
    header('Content-Type: image/jpeg');
    $img = 'img/placeholders/user.png';
} else {
    header('Content-Type: image/png');
    $img = "../videos/userPhoto/photo{$_REQUEST['users_id']}.png";
}

if (!file_exists($img)) {
    header('Content-Type: image/jpeg');
    $img = 'img/placeholders/user.png';
}

header('Content-Length: ' . filesize($img));
//header("X-Sendfile: ../{$img}");
//exit;

//echo $img;
readfile($img);

//header("Location: {$photo}");
exit;
