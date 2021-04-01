<?php

global $global, $config;
session_write_close();
$obj = new stdClass();
$obj->error = true;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::canUpload()) {
    $obj->msg = "Only logged users can upload";
    die(json_encode($obj));
}

header('Content-Type: application/json');

// A list of permitted file extensions

$allowed = Video::$types;

$advancedCustom = AVideoPlugin::getObjectDataIfEnabled("CustomizeAdvanced");

if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), $allowed)) {
        $obj->msg = "File extension error [{$_FILES['upl']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }

    require_once $global['systemRootPath'] . 'objects/video.php';

    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);
    $path_parts = pathinfo($_FILES['upl']['name']);
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($path_parts['filename']));
    $filename = uniqid($mainName . "_", true);
    $videos_id = 0;
    if (!empty($_FILES['upl']['videoId'])) {
        $videos_id = $_FILES['upl']['videoId'];
    } else if (!empty($_POST['videos_id'])) {
        $videos_id = $_POST['videos_id'];
    }
    $title = preg_replace("/_+/", " ", str_ireplace(".{$extension}", "", $_FILES['upl']['name']));
    if (empty($videos_id)) {
        $video = new Video($title, $filename, 0);
    } else {
        $video = new Video("", $filename, $videos_id);
        if ($video->getTitle() === "Video automatically booked") {
            $video->setTitle($title);
            $video->setStatus('i');
        }
    }
    //var_dump($videos_id, $_FILES['upl']['name'], $title, $video->getTitle());exit;
    $video->setDuration($duration);

    if (!empty($_POST['title'])) {
        $video->setTitle($_POST['title']);
    }

    if (!empty($_POST['description'])) {

        if (strip_tags($_POST['description']) === $_POST['description']) {
            $_POST['description'] = nl2br(textToLink($_POST['description']));
        }
        $video->setDescription($_POST['description']);
    }

    if ($extension == "mp4" || $extension == "webm") {
        if (!empty($advancedCustom->disableMP4Upload)) {
            $obj->msg = "Video Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("video", true);
    } else
    if (($extension == "mp3") || ($extension == "ogg")) {
        if (!empty($advancedCustom->disableMP3Upload)) {
            $obj->msg = "MP3 Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("audio", true);
    } else
    if (($extension == "pdf")) {
        if (!empty($advancedCustom->disablePDFUpload)) {
            $obj->msg = "PDF Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("pdf", true);
    }
    if (($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif" || $extension == "webp")) {
        if (!empty($advancedCustom->disableImageUpload)) {
            $obj->msg = "Images Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("image", true);
    }
    if (($extension == "zip")) {
        if (!empty($global['disableAdvancedConfigurations'])) {
            $obj->msg = "Zip is disabled on this server";
            die(json_encode($obj));
        }
        if (!empty($advancedCustom->disableZipUpload)) {
            $obj->msg = "Zip Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("zip", true);
    }
    if (empty($advancedCustom->makeVideosInactiveAfterEncode) && $video->getTitle() !== "Video automatically booked") {

        // set active

        $video->setStatus('a');
    } else if (empty($advancedCustom->makeVideosUnlistedAfterEncode) && $video->getTitle() !== "Video automatically booked") {

        // set active

        $video->setStatus('u');
    } else {
        $video->setStatus('i');
    }

    $id = $video->save();
    if ($id) {

        /**
         * This is when is using in a non uploaded movie
         */
        $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
        $tmp_name = $_FILES['upl']['tmp_name'];
        $filenameMP4 = $filename . "." . $extension;
        decideMoveUploadedToVideos($tmp_name, $filenameMP4, $video->getType());

        if ((AVideoPlugin::isEnabledByName('MP4ThumbsAndGif')) && ($extension == "mp4" || $extension == "webm" || $extension == "mp3")) {

            $videoFileName = $video->getFilename();

            MP4ThumbsAndGif::getImage($videoFileName, 'jpg', $id);
            MP4ThumbsAndGif::getImage($videoFileName, 'gif', $id);
            MP4ThumbsAndGif::getImage($videoFileName, 'webp', $id);
        }

        //    } else if(($extension=="mp3")||($extension=="ogg")){
        //  }
        $obj->title = $video->getTitle();
        $obj->error = false;
        $obj->filename = $filename;
        $obj->duration = $duration;
        $obj->videos_id = $id;


        if (!empty($_FILES['upl']['tmp_name'])) {
            AVideoPlugin::afterNewVideo($obj->videos_id);
        }


        if ($extension !== "jpg" && $video->getType() == "image") {
            sleep(1); // to make sure the file will be available
            $file = Video::getStoragePath() . "" . $video->getFilename();
            try {
                convertImage("{$file}.{$extension}", "{$file}.jpg", 70);
            } catch (Exception $exc) {
                _error_log("We could not convert the image to JPG " . $exc->getMessage());
            }
        }

        die(json_encode($obj));
    }
}

$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
