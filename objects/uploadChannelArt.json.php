<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header('Content-Type: application/json');

if (!User::isLogged()) {
    forbiddenPage('Login required');
}

require_once $global['systemRootPath'] . 'objects/channelImageUpload.php';
$obj = new stdClass();
$obj->error = true;
$obj->msg = __('An error occurred');
$obj->variations = [];
try {
    $directory = 'videos/userPhoto/' . User::getId() . '/';
    $relativePath = $directory . 'background.jpg';
    $variations = [];
    foreach (User::$channel_art as $value) {
        $path = $directory . 'background_' . $value[0] . '_.jpg';
        $obj->variations[$value[0]] = $path;
        $variations[] = [$global['systemRootPath'] . $path, $value[1], $value[2]];
    }
    saveChannelImageUpload($global['systemRootPath'] . $relativePath, 2560, 1440, $variations, 'image', function () use ($relativePath) {
        $user = new User(User::getId());
        $user->setBackgroundURL($relativePath);
        // The channel page finds the cover by its path; only User::getBackground() reads this column,
        // so a failed account save must not undo an upload that never depended on it.
        if (!$user->save()) {
            _error_log('uploadChannelArt: cannot update backgroundURL for user ' . User::getId(), AVideoLog::$WARNING);
        }
        return true;
    });
    User::updateSessionInfo();
    clearCache(true);
    $obj->imagePNGResponse = filesize($global['systemRootPath'] . $relativePath);
    $obj->imageJPGResponse = true;
    $obj->url = getURL($relativePath, true);
    $obj->previewURL = getURL($obj->variations[User::$channel_artDesktopMax], true);
    $obj->error = false;
    $obj->msg = __('Saved');
    if (User::isAdmin()) {
        $obj->imageJPG = $global['systemRootPath'] . $relativePath;
        $obj->imagePNG = $obj->imageJPG . '.png';
    }
} catch (InvalidArgumentException $th) {
    $obj->msg = $th->getMessage();
} catch (Throwable $th) {
    _error_log('uploadChannelArt: ' . $th->getMessage());
}
die(json_encode($obj));
