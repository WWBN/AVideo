<?php

// Fetch requested image URL
$imageURL = !empty($_GET['image']) ? $_GET['image'] : $_SERVER["REQUEST_URI"];
$rootDir = dirname(__FILE__) . '/../../';
if($imageURL == 'favicon.ico'){
    $imgLocalFile = "{$rootDir}/videos/{$imageURL}";
}else{
    $imgLocalFile = "{$rootDir}/{$imageURL}";
}

if (file_exists($imgLocalFile)) {
    $imageInfo = getimagesize($imgLocalFile);
    if (empty($imageInfo)) {
        die('not image');
    }
    // Determine the content type based on the file extension
    $fileExtension = strtolower(pathinfo($imgLocalFile, PATHINFO_EXTENSION));
    switch ($fileExtension) {
        case 'jpg':
        case 'jpeg':
            $type = 'image/jpeg';
            break;
        case 'png':
            $type = 'image/png';
            break;
        case 'webp':
            $type = 'image/webp';
            break;
        case 'gif':
            $type = 'image/gif';
            break;
        default:
            $type = 'image/jpeg'; // Default to jpg if the extension is not recognized
            break;
    }

    // Serve the final image
    header("HTTP/1.0 200 OK"); // The image exists, so it's not a 404
    header('Content-Type: ' . $type);
    header('Content-Length: ' . filesize($imgLocalFile));
    readfile($imgLocalFile);
    exit;
} 
