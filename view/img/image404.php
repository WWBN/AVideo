<?php

include(dirname(__FILE__) . '/image404Raw.php');
$doNotIncludeConfig = 1;
// Load configuration
$configFile = dirname(__FILE__) . '/../../videos/configuration.php';

if(!file_exists($configFile)){
    return;
}

require_once $configFile;

//_session_write_close();

// Default image settings
$file = ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_PATH);
$type = 'image/png';

// Fetch requested image URL
$imageURL = !empty($_GET['image']) ? $_GET['image'] : $_SERVER["REQUEST_URI"];

// Handle Thumbnails
if (preg_match('/videos\/(.*\/)?(.*)_thumbs(V2)?.jpg/', $imageURL, $matches)) {
    $video_filename = $matches[2];

    $jpg = "{$global['systemRootPath']}videos/{$video_filename}/{$video_filename}.jpg";

    if (file_exists($jpg)) {
        $file = $jpg;
        $type = 'image/jpg';

        if (strpos($imageURL, '_thumbsV2') !== false) {
            $imgDestination = "{$global['systemRootPath']}{$imageURL}";
            if(!file_exists($imgDestination)){
                error_log("Converting thumbnail: {$jpg} to {$imgDestination}");
                convertThumbsIfNotExists($jpg, $imgDestination);
            }
        }
    } else {
        error_log("Thumbnail image not found: {$imageURL} {$jpg}");
    }
// Handle Roku Images
} elseif (preg_match('/videos\/(.*\/)?(.*)_roku.jpg/', $imageURL, $matches)) {
    $video_filename = $matches[2];
    $jpg = Video::getPathToFile("{$video_filename}.jpg");

    if (file_exists($jpg)) {
        $file = $jpg;
        $type = 'image/jpg';

        if (strpos($imageURL, '_roku') !== false) {
            $rokuDestination = "{$global['systemRootPath']}{$imageURL}";            
            if(!file_exists($rokuDestination)){
                error_log("Converting for Roku: {$jpg} to {$rokuDestination}");
                convertImageToRoku($jpg, $rokuDestination);
            }
        }

    } else {
        error_log("Roku image not found: {$imageURL}");
    }

} else {
    if(
        preg_match('/filename\/filename_/', $imageURL) OR
        preg_match('/undefined\/undefined/', $imageURL) OR
        preg_match('/image404.php/', $imageURL)
    ){

    }else{
        error_log("Unmatched image request: {$imageURL}");
    }
}

// If a 404 image needs to be shown, redirect to it
if (empty($_GET['notFound']) && ImagesPlaceHolders::isDefaultImage($file)) {
    header("Location: " . getCDN() . "view/img/image404.php?notFound=1");
    exit;
}

// Serve the final image
if(ImagesPlaceHolders::isDefaultImage($file)){
    header("HTTP/1.0 404 Not Found");
}else{
    header("HTTP/1.0 200 OK");
}

$imageInfo = getimagesize($file);
if (empty($imageInfo)) {
    die('not image');
}

header('Content-Type:' . $type);
header('Content-Length: ' . filesize($file));
readfile($file);

?>
