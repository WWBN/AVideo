<?php

function getCroppieElement(
    $buttonTitle,
    $callBackJSFunction,
    $resultWidth = 0,
    $resultHeight = 0,
    $viewportWidth = 0,
    $boundary = 25,
    $viewportHeight = 0,
    $enforceBoundary = true
) {
    global $global;

    if (empty($resultWidth) && empty($resultHeight)) {
        if (isMobile()) {
            $viewportWidth = 250;
        } else {
            $viewportWidth = 800;
        }

        if (defaultIsPortrait()) {
            $resultWidth = 540;
            $resultHeight = 800;
        } else {
            $resultWidth = 1280;
            $resultHeight = 720;
        }
    }

    if (empty($viewportWidth)) {
        $viewportWidth = $resultWidth;
    }
    $zoom = 0;
    if (empty($viewportHeight)) {
        $zoom = ($viewportWidth / $resultWidth);
        $viewportHeight = $zoom * $resultHeight;
    }
    if (empty($enforceBoundary)) {
        $boundary = 0;
    }
    $boundaryWidth = $viewportWidth + $boundary;
    $boundaryHeight = $viewportHeight + $boundary;
    $uid = uniqid();

    $varsArray = [
        'buttonTitle' => $buttonTitle,
        'callBackJSFunction' => $callBackJSFunction,
        'resultWidth' => $resultWidth,
        'resultHeight' => $resultHeight,
        'viewportWidth' => $viewportWidth,
        'boundary' => $boundary,
        'viewportHeight' => $viewportHeight,
        'enforceBoundary' => $enforceBoundary,
        'zoom' => $zoom,
        'boundaryWidth' => $boundaryWidth,
        'boundaryHeight' => $boundaryHeight,
        'uid' => $uid,
    ];

    $contents = getIncludeFileContent($global['systemRootPath'] . 'objects/functionCroppie.js.php', $varsArray);

    $callBackJSFunction = addcslashes($callBackJSFunction, "'");
    return [
        "html" => $contents,
        "id" => "croppie{$uid}",
        "uploadCropObject" => "uploadCrop{$uid}",
        "getCroppieFunction" => "getCroppie(uploadCrop{$uid}, '{$callBackJSFunction}', {$resultWidth}, {$resultHeight});",
        "createCroppie" => "createCroppie{$uid}",
        "restartCroppie" => "restartCroppie{$uid}",
    ];
}

function saveCroppieImageElement($destination, $postIndex = "imgBase64")
{
    if (empty($destination) || empty($_POST[$postIndex])) {
        return false;
    }
    $fileData = base64DataToImage($_POST[$postIndex]);

    $path_parts = pathinfo($destination);
    $tmpDestination = $destination;
    $extension = mb_strtolower($path_parts['extension']);
    if ($extension !== 'png') {
        $tmpDestination = $destination . '.png';
    }

    $saved = _file_put_contents($tmpDestination, $fileData);

    if ($saved) {
        if ($extension !== 'png') {
            convertImage($tmpDestination, $destination, 100);
            unlink($tmpDestination);
        }
    }
    //var_dump($saved, $tmpDestination, $destination, $extension);exit;
    return $saved;
}
