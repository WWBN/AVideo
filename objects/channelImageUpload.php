<?php

/** Save a normalized channel image and its center crops, staging every output first. */
function saveChannelImageUpload($destination, $width, $height, $variations = [], $postIndex = 'image', $onSaved = null)
{
    $input = $_POST[$postIndex] ?? null;
    if (!is_string($input)) {
        throw new InvalidArgumentException(__('Invalid image'));
    }
    $data = getValidatedImageBinaryFromBase64($input, 20971520, $message);
    if ($data === false) {
        throw new InvalidArgumentException($message);
    }
    $size = @getimagesizefromstring($data);
    if (!$size || $size[0] > 16384 || $size[1] > 16384 || $size[0] * $size[1] > 40000000) {
        throw new InvalidArgumentException(__('Image too large or invalid'));
    }

    make_path(dirname($destination) . '/');
    $source = $canvas = false;
    $staged = [];
    $backups = [];
    $installed = [];
    $lock = fopen(dirname($destination) . '/.channel-images.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) {
        if ($lock) {
            fclose($lock);
        }
        throw new RuntimeException('Cannot lock channel image upload');
    }
    try {
        $source = @imagecreatefromstring($data);
        if (!$source) {
            throw new InvalidArgumentException(__('Invalid image'));
        }
        $canvas = imagecreatetruecolor($width, $height);
        $isPNG = strtolower(pathinfo($destination, PATHINFO_EXTENSION)) === 'png';
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, $isPNG ? 127 : 0));
        $scale = max($width / $size[0], $height / $size[1]);
        $cropWidth = (int) round($width / $scale);
        $cropHeight = (int) round($height / $scale);
        imagealphablending($canvas, !$isPNG);
        imagecopyresampled($canvas, $source, 0, 0, (int) (($size[0] - $cropWidth) / 2), (int) (($size[1] - $cropHeight) / 2), $width, $height, $cropWidth, $cropHeight);

        $outputs = [[$destination, $width, $height]];
        foreach ($variations as $variation) {
            $outputs[] = $variation;
        }
        foreach ($outputs as $output) {
            list($path, $cropWidth, $cropHeight) = $output;
            if ($cropWidth > $width || $cropHeight > $height || is_dir($path)) {
                throw new RuntimeException('Invalid channel image destination');
            }
            $crop = imagecrop($canvas, ['x' => (int) (($width - $cropWidth) / 2), 'y' => (int) (($height - $cropHeight) / 2), 'width' => $cropWidth, 'height' => $cropHeight]);
            if (!$crop) {
                throw new RuntimeException('Cannot crop channel image');
            }
            $temporary = tempnam(dirname($path), '.channel-image-');
            if ($temporary === false) {
                if (is_resource($crop)) { imagedestroy($crop); }
                $crop = null;
                throw new RuntimeException('Cannot stage channel image');
            }
            $staged[$path] = $temporary;
            imagesavealpha($crop, true);
            $saved = $isPNG ? imagepng($crop, $temporary) : imagejpeg($crop, $temporary, 90);
            if (is_resource($crop)) { imagedestroy($crop); }
            $crop = null;
            if (!$saved || !filesize($temporary)) {
                throw new RuntimeException('Cannot write channel image');
            }
            // tempnam creates mode 0600; images must remain readable by the web server/CDN.
            chmod($temporary, 0644);
        }
        foreach ($staged as $path => $temporary) {
            if (is_file($path)) {
                $backup = tempnam(dirname($path), '.channel-backup-');
                if ($backup === false) {
                    throw new RuntimeException('Cannot preserve channel image');
                }
                $backups[$path] = $backup;
                if (!copy($path, $backup)) {
                    throw new RuntimeException('Cannot preserve channel image');
                }
                chmod($backup, fileperms($path) & 0777);
            }
            if (!rename($temporary, $path)) {
                throw new RuntimeException('Cannot replace channel image');
            }
            $installed[] = $path;
            clearstatcache(true, $path);
        }
        if ($onSaved !== null && !$onSaved()) {
            throw new RuntimeException('Cannot update channel image account');
        }
        return true;
    } catch (Throwable $th) {
        foreach (array_reverse($installed) as $path) {
            if (isset($backups[$path])) {
                if (!rename($backups[$path], $path)) {
                    _error_log('Cannot restore channel image ' . $path);
                    // Retain the backup for recovery if the filesystem also refuses rollback.
                    unset($backups[$path]);
                }
            } else {
                unlink($path);
            }
        }
        throw $th;
    } finally {
        foreach (array_merge(array_values($staged), array_values($backups)) as $temporary) {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
        if (is_resource($source)) {
            imagedestroy($source);
        }
        if (is_resource($canvas)) {
            imagedestroy($canvas);
        }
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
