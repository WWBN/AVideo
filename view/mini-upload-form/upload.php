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
    $filename = _uniqid($mainName . "_", true);
    $videos_id = 0;
    if (!empty($_FILES['upl']['videoId'])) {
        $videos_id = $_FILES['upl']['videoId'];
    } elseif (!empty($_REQUEST['videos_id'])) {
        $videos_id = $_REQUEST['videos_id'];
    }
    $title = preg_replace("/_+/", " ", str_ireplace(".{$extension}", "", $_FILES['upl']['name']));
    if (empty($videos_id)) {
        $video = new Video($title, $filename, 0);
    } else {
        $video = new Video("", "", $videos_id);
        $filename = $video->getFilename();
        if ($video->getTitle() === "Video automatically booked") {
            $video->setTitle($title);
            $video->setStatus(Video::$statusInactive);
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
    } elseif (($extension == "mp3") || ($extension == "ogg")) {
        if (!empty($advancedCustom->disableMP3Upload)) {
            $obj->msg = "MP3 Files are not Allowed";
            die(json_encode($obj));
        }
        $video->setType("audio", true);
    } elseif (($extension == "pdf")) {
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

    $video->setAutoStatus(Video::$statusInactive);

    $id = $video->save();
    if ($id) {

        /**
         * This is when is using in a non uploaded movie
         */
        $aws_s3 = AVideoPlugin::loadPluginIfEnabled('AWS_S3');
        $tmp_name = $_FILES['upl']['tmp_name'];
        $filenameMP4 = $filename . "." . $extension;
        decideMoveUploadedToVideos($tmp_name, $filenameMP4, $video->getType());
        
        $obj->title = $video->getTitle();
        $obj->error = false;
        $obj->filename = $filename;
        $obj->duration = $duration;
        $obj->videos_id = $id;
        $obj->lines = [];
        //var_dump($obj->videos_id);exit;
        if ($extension !== "jpg" && $video->getType() == "image") {
            $obj->lines[] = __LINE__;
            sleep(1); // to make sure the file will be available
            $file = $video->getFilename();
            $jpgFrom = Video::getPathToFile("{$file}.{$extension}");
            $jpgTo = Video::getPathToFile("{$file}.jpg");
            try {
                $obj->lines[] = __LINE__;
                convertImage($jpgFrom, $jpgTo, 70);
            } catch (Exception $exc) {
                $obj->lines[] = __LINE__;
                _error_log("We could not convert the image to JPG " . $exc->getMessage());
            }
        }
        $obj->lines[] = __LINE__;

        if (!empty($_FILES['upl']['tmp_name'])) {
            $obj->lines[] = __LINE__;
            $video->setAutoStatus(Video::$statusActive);
            AVideoPlugin::onUploadIsDone($obj->videos_id);
            AVideoPlugin::afterNewVideo($obj->videos_id);
        }
        $obj->lines[] = __LINE__;
        die(json_encode($obj));
    }
}

$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));
