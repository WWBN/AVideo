<?php
require_once __DIR__ . '/../videos/configuration.php';
header('Content-Type: application/json');

$obj = new stdClass();
$obj->msg = '';
$obj->error = true;
$obj->filename = '';
$obj->url = '';

if (!User::isLogged()) {
    forbiddenPage('You must be logged in');
}

$userId = User::getId();

if (!empty($_REQUEST['videos_id'])) {
    $relativeDir = Video::getVideoLibRelativePath($_REQUEST['videos_id']);
} else {
    $relativeDir = "videos/userPhoto/Live/user_{$userId}/";
}


$absoluteDir = __DIR__ . '/../' . $relativeDir;

make_path($relativeDir);

if (!is_uploaded_file($_FILES["upl"]["tmp_name"])) {
    forbiddenPage('Possible file upload attack');
}

$ext = strtolower(pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
    forbiddenPage('Invalid file type');
}

$maxSizeMB = 5;
if ($_FILES['upl']['size'] > $maxSizeMB * 1024 * 1024) { // 2MB
    forbiddenPage("File too large (max {$maxSizeMB}MB)");
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['upl']['tmp_name']);
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
    forbiddenPage('Invalid file type');
}
finfo_close($finfo);

$filename = uniqid('img_', true) . '.' . $ext;
$target_file = $absoluteDir . $filename;

if (move_uploaded_file($_FILES["upl"]["tmp_name"], $target_file)) {
    $obj->error = false;
    $obj->msg = 'Upload successful';
    $obj->filename = $filename;
    $obj->url = $global['webSiteRootURL'] . $relativeDir . $filename;
} else {
    $obj->msg = 'Failed to upload file';
}

echo json_encode($obj);
