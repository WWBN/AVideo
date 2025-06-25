<?php
require_once __DIR__ . '/../videos/configuration.php';

if (!User::isLogged()) {
    forbiddenPage('You must be logged in to access this page');
}

$userId = User::getId();
$videos_id = getVideos_id();

// List of relative directories (must end with slash)
if($videos_id){
    $relativeDirs = [
        Video::getVideoLibRelativePath($videos_id),
    ];
}else{
    $relativeDirs = [
        "videos/userPhoto/Live/user_{$userId}/",
    ];
}

$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$images = [];

foreach ($relativeDirs as $relativeDir) {
    $absoluteDir = realpath(__DIR__ . "/../{$relativeDir}");

    // Security check: must be valid and inside videos folder
    if (!$absoluteDir || strpos($absoluteDir, realpath(__DIR__ . '/../videos/')) !== 0) {
        continue;
    }

    if (!is_dir($absoluteDir)) {
        continue;
    }

    foreach (scandir($absoluteDir) as $file) {
        $path = realpath($absoluteDir . DIRECTORY_SEPARATOR . $file);

        // Skip if not a valid file or outside the intended directory
        if (!$path || strpos($path, $absoluteDir) !== 0) {
            continue;
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (is_file($path) && in_array($ext, $allowed_exts)) {
            $images[] = [
                'url' => $global['webSiteRootURL'] . $relativeDir . $file,
                'filename' => $file,
                'relativeDir' => $relativeDir
            ];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($images);
