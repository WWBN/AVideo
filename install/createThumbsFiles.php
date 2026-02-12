<?php
//streamer config
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/functionsImages.php';

if (!isCommandLineInterface()) {
    return die('Command Line only');
}

ob_end_flush();

$advancedCustom = AVideoPlugin::getObjectData("CustomizeAdvanced");

$sql = "SELECT * FROM videos WHERE status IN ('a', 'k', 'f', 'u', 'i') ORDER BY id DESC";
$res = sqlDAL::readSql($sql);
$fullData = sqlDAL::fetchAllAssoc($res);
$total = count($fullData);
sqlDAL::close($res);

echo "Total videos to process: {$total}" . PHP_EOL;
echo "Thumbs size: {$advancedCustom->thumbsWidthLandscape}x{$advancedCustom->thumbsHeightLandscape}" . PHP_EOL;
echo PHP_EOL;

$createdCount = 0;
$skippedCount = 0;
$errorCount = 0;

if ($res != false) {
    $count = 0;
    foreach ($fullData as $key => $row) {
        $count++;
        $filename = $row['filename'];
        $videos_id = $row['id'];
        
        if (empty($filename)) {
            echo "[{$count}/{$total}] SKIP: empty filename for video ID {$videos_id}" . PHP_EOL;
            $skippedCount++;
            continue;
        }
        
        // Get paths
        $jpegSource = Video::getSourceFile($filename, ".jpg", false, true);
        $thumbsSource = Video::getSourceFile($filename, "_thumbsV2.jpg", false, true);
        
        if (empty($jpegSource) || empty($jpegSource['path'])) {
            echo "[{$count}/{$total}] SKIP: no source .jpg for {$filename}" . PHP_EOL;
            $skippedCount++;
            continue;
        }
        
        $sourcePath = $jpegSource['path'];
        $thumbsPath = $thumbsSource['path'];
        $webpPath = str_replace('.jpg', '_jpg.webp', $thumbsPath);
        
        // Check if source exists
        if (!file_exists($sourcePath)) {
            echo "[{$count}/{$total}] SKIP: source not found {$sourcePath}" . PHP_EOL;
            $skippedCount++;
            continue;
        }
        
        $createdThisVideo = false;
        
        // Create _thumbsV2.jpg if not exists
        if (!file_exists($thumbsPath) || filesize($thumbsPath) < 1024) {
            echo "[{$count}/{$total}] Creating _thumbsV2.jpg for {$filename}..." . PHP_EOL;
            
            $result = convertImageIfNotExists(
                $sourcePath, 
                $thumbsPath, 
                $advancedCustom->thumbsWidthLandscape, 
                $advancedCustom->thumbsHeightLandscape, 
                true
            );
            
            if (file_exists($thumbsPath)) {
                // Change owner to www-data
                @chown($thumbsPath, 'www-data');
                @chgrp($thumbsPath, 'www-data');
                @chmod($thumbsPath, 0644);
                
                echo "  -> Created: {$thumbsPath} (" . humanFileSize(filesize($thumbsPath)) . ")" . PHP_EOL;
                $createdThisVideo = true;
            } else {
                echo "  -> ERROR: Failed to create _thumbsV2.jpg" . PHP_EOL;
                $errorCount++;
            }
        }
        
        // Create _thumbsV2_jpg.webp if not exists (only if _thumbsV2.jpg exists)
        if (file_exists($thumbsPath) && (!file_exists($webpPath) || filesize($webpPath) < 1024)) {
            if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
                echo "[{$count}/{$total}] Creating _thumbsV2_jpg.webp for {$filename}..." . PHP_EOL;
                
                convertImage($thumbsPath, $webpPath, 90);
                
                if (file_exists($webpPath)) {
                    // Change owner to www-data
                    @chown($webpPath, 'www-data');
                    @chgrp($webpPath, 'www-data');
                    @chmod($webpPath, 0644);
                    
                    echo "  -> Created: {$webpPath} (" . humanFileSize(filesize($webpPath)) . ")" . PHP_EOL;
                    $createdThisVideo = true;
                } else {
                    echo "  -> ERROR: Failed to create _thumbsV2_jpg.webp" . PHP_EOL;
                    $errorCount++;
                }
            } else {
                echo "[{$count}/{$total}] SKIP webp: PHP version < 8.0" . PHP_EOL;
            }
        }
        
        if ($createdThisVideo) {
            $createdCount++;
            // Clear cache for this video
            Video::clearImageCache($filename);
            Video::clearImageCache($filename, 'video');
        } else {
            if (file_exists($thumbsPath) && file_exists($webpPath)) {
                // Both files already exist
                $skippedCount++;
            }
        }
    }
} else {
    die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo "SUMMARY:" . PHP_EOL;
echo "  Total videos processed: {$total}" . PHP_EOL;
echo "  Videos with thumbs created: {$createdCount}" . PHP_EOL;
echo "  Videos skipped (already ok or no source): {$skippedCount}" . PHP_EOL;
echo "  Errors: {$errorCount}" . PHP_EOL;
echo "========================================" . PHP_EOL;
