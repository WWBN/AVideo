<?php
require_once __DIR__ . '/../videos/configuration.php';

header('Content-Type: application/json');
$response = ['error' => true];

if (!User::isLogged()) {
    $response['msg'] = 'Not logged in';
    die(json_encode($response));
}

$userId = User::getId();

if (!empty($_REQUEST['videos_id'])) {
    $relativeDir = Video::getVideoLibRelativePath($_REQUEST['videos_id']);
} else {
    $relativeDir = "videos/userPhoto/Live/user_{$userId}/";
}

$absoluteDir = realpath(__DIR__ . "/../{$relativeDir}");

if (!is_dir($absoluteDir)) {
    $response['msg'] = 'Directory not found';
    die(json_encode($response));
}

$filename = basename($_POST['filename'] ?? '');

$files = scandir($absoluteDir);
if (!in_array($filename, $files)) {
    forbiddenPage('Invalid filename');
}

$fullPath = realpath("{$absoluteDir}/{$filename}");

if (!$fullPath || strpos($fullPath, $absoluteDir) !== 0 || !file_exists($fullPath)) {
    $response['msg'] = 'Invalid file';
    die(json_encode($response));
}

if (unlink($fullPath)) {
    $jsonPath = preg_replace('/\.(jpg|jpeg|png|gif|webp)$/i', '.json', $fullPath);
    if (file_exists($jsonPath)) {
        unlink($jsonPath);
    }
    $response['error'] = false;
    $response['msg'] = 'File deleted';
} else {
    $response['msg'] = 'Failed to delete file';
}

echo json_encode($response);
