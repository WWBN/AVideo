<?php
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
allowOrigin();

$global['bypassSameDomainCheck'] = 1;
inputToRequest();
_error_log("REQUEST: " . json_encode($_REQUEST));
_error_log("POST: " . json_encode($_REQUEST));
_error_log("GET: " . json_encode($_GET));

if (empty($_REQUEST)) {
    $obj->msg = ("Your REQUEST data is empty, maybe your video file is too big for the host");
    _error_log("ReceiveImage: " . $obj->msg);
    die(json_encode($obj));
}

useVideoHashOrLogin();
if (!User::canUpload()) {
    $obj->msg = __("Permission denied to receive a image: " . json_encode($_REQUEST));
    _error_log("ReceiveImage: " . $obj->msg);
    die(json_encode($obj));
}

if (!Video::canEdit($_REQUEST['videos_id'])) {
    $obj->msg = __("Permission denied to edit a video: " . json_encode($_REQUEST));
    _error_log("ReceiveImage: " . $obj->msg);
    die(json_encode($obj));
}

$securityChecks = array(
    'downloadURL_gifimage',
    'downloadURL_webpimage',
    'downloadURL_image',
    'downloadURL_spectrumimage',
);

foreach ($securityChecks as $key => $value) {
    if(!empty($_REQUEST[$value])){
        $_REQUEST[$value] = str_replace('../', '', $_REQUEST[$value]);
    }
}

_error_log("ReceiveImage: Start receiving image " . json_encode($_FILES) . "" . json_encode($_REQUEST));
// check if there is en video id if yes update if is not create a new one
$video = new Video("", "", $_REQUEST['videos_id'], true);
$obj->video_id = $_REQUEST['videos_id'];

$videoFileName = $video->getFilename();
$paths = Video::getPaths($videoFileName, true);
$destination_local = "{$paths['path']}{$videoFileName}";

make_path($destination_local);

_error_log("ReceiveImage: videoFilename = [$videoFileName] destination_local = {$destination_local} Encoder receiving post " . json_encode($_FILES));

$obj->jpgDest = "{$destination_local}.jpg";
if (!file_exists($obj->jpgDest) || !fileIsAnValidImage($obj->jpgDest)) {

    if (isValidURL($_REQUEST['downloadURL_image'])) {
        $content = url_get_contents($_REQUEST['downloadURL_image']);
        $obj->jpgDestSize = _file_put_contents($obj->jpgDest, $content);
        _error_log("ReceiveImage: download {$_REQUEST['downloadURL_image']} to {$obj->jpgDest} " . humanFileSize($obj->jpgDestSize));
    } elseif (!empty($_FILES['image']['tmp_name']) && (!empty($_REQUEST['update_video_id']) || !fileIsAnValidImage($obj->jpgDest))) {
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $obj->jpgDest)) {
            if (!rename($_FILES['image']['tmp_name'], $obj->jpgDest)) {
                if (!copy($_FILES['image']['tmp_name'], $obj->jpgDest)) {
                    if (!file_exists($_FILES['image']['tmp_name'])) {
                        $obj->msg = print_r(sprintf(__("Could not move image file because it does not exits %s => [%s]"), $_FILES['image']['tmp_name'], $obj->jpgDest), true);
                    } else {
                        $obj->msg = print_r(sprintf(__("Could not move image file %s => [%s]"), $_FILES['image']['tmp_name'], $obj->jpgDest), true);
                    }
                    _error_log("ReceiveImage: " . $obj->msg);
                    die(json_encode($obj));
                }
            }
        } else {
            $obj->jpgDestSize = humanFileSize(filesize($obj->jpgDest));
        }
    } else {
        if (empty($_FILES['image']['tmp_name'])) {
            _error_log("ReceiveImage: empty \$_FILES['image']['tmp_name'] " . json_encode($_FILES));
        }
        if (file_exists($obj->jpgDest)) {
            _error_log("ReceiveImage: File already exists " . $obj->jpgDest);
            if (fileIsAnValidImage($obj->jpgDest)) {
                _error_log("ReceiveImage: file is not an error image " . filesize($obj->jpgDest));
            }
        }
    }
}

if (!empty($_REQUEST['downloadURL_spectrumimage'])) {
    $content = url_get_contents($_REQUEST['downloadURL_spectrumimage']);
    $obj->jpgSpectrumDestSize = _file_put_contents($obj->jpgSpectrumDest, $content);
    _error_log("ReceiveImage: download {$_REQUEST['downloadURL_spectrumimage']} {$obj->jpgDestSize}");
} elseif (!empty($_FILES['spectrumimage']['tmp_name'])) {
    $obj->jpgSpectrumDest = "{$destination_local}_spectrum.jpg";
    if ((!empty($_REQUEST['update_video_id']) || !fileIsAnValidImage($obj->jpgSpectrumDest))) {
        if (!move_uploaded_file($_FILES['spectrumimage']['tmp_name'], $obj->jpgSpectrumDest)) {
            $obj->msg = print_r(sprintf(__("Could not move image file [%s.jpg]"), $destination_local), true);
            _error_log("ReceiveImage: " . $obj->msg);
            die(json_encode($obj));
        } else {
            $obj->jpgSpectrumDestSize = humanFileSize(filesize($obj->jpgSpectrumDest));
        }
    } else {
        if (empty($_FILES['spectrumimage']['tmp_name'])) {
            _error_log("ReceiveImage: empty \$_FILES['spectrumimage']['tmp_name'] " . json_encode($_FILES));
        }
        if (file_exists($obj->jpgSpectrumDest)) {
            _error_log("ReceiveImage: File already exists " . $obj->jpgDest);
            if (fileIsAnValidImage($obj->jpgSpectrumDestSize)) {
                _error_log("ReceiveImage: file is not an error image " . filesize($obj->jpgDest));
            }
        }
    }
}

$obj->gifDest = "{$destination_local}.gif";
if (!empty($_REQUEST['downloadURL_gifimage'])) {
    $content = url_get_contents($_REQUEST['downloadURL_gifimage']);
    $obj->gifDestSize = file_put_contents($obj->gifDest, $content);
    _error_log("ReceiveImage: download {$_REQUEST['downloadURL_gifimage']} {$obj->gifDestSize}");
} elseif (!empty($_FILES['gifimage']['tmp_name']) && (!empty($_REQUEST['update_video_id']) || !file_exists($obj->gifDest) || filesize($obj->gifDest) === 2095341)) {
    if (!move_uploaded_file($_FILES['gifimage']['tmp_name'], $obj->gifDest)) {
        $obj->msg = print_r(sprintf(__("Could not move gif image file [%s.gif]"), $destination_local), true);
        _error_log("ReceiveImage: " . $obj->msg);
        die(json_encode($obj));
    } else {
        $obj->gifDestSize = humanFileSize(filesize($obj->gifDest));
    }
} else {
    if (empty($_FILES['gifimage']['tmp_name'])) {
        _error_log("ReceiveImage: empty \$_FILES['gifimage']['tmp_name'] " . json_encode($_FILES));
    }
    if (file_exists($obj->gifDest)) {
        _error_log("ReceiveImage: File already exists " . $obj->gifDest);
        if (fileIsAnValidImage($obj->gifDest)) {
            _error_log("ReceiveImage: file is not an error image " . filesize($obj->gifDest));
        }
    }
}

$obj->webpDest = "{$destination_local}.webp";
if (!empty($_REQUEST['downloadURL_webpimage'])) {
    $content = url_get_contents($_REQUEST['downloadURL_webpimage']);
    $obj->webpDestSize = file_put_contents($obj->webpDest, $content);
    _error_log("ReceiveImage: download {$_REQUEST['downloadURL_webpimage']} {$obj->webpDestSize}");
} elseif (!empty($_FILES['webpimage']['tmp_name']) && (!empty($_REQUEST['update_video_id']) || !file_exists($obj->webpDest) || filesize($obj->webpDest) === 2095341)) {
    if (!move_uploaded_file($_FILES['webpimage']['tmp_name'], $obj->webpDest)) {
        $obj->msg = print_r(sprintf(__("Could not move webp image file [%s.webp]"), $destination_local), true);
        _error_log("ReceiveImage: " . $obj->msg);
        die(json_encode($obj));
    } else {
        $obj->webpDestSize = humanFileSize(filesize($obj->webpDest));
    }
} else {
    if (empty($_FILES['webpimage']['tmp_name'])) {
        _error_log("ReceiveImage: empty \$_FILES['webpimage']['tmp_name'] " . json_encode($_FILES));
    }
    if (file_exists($obj->webpDest)) {
        _error_log("ReceiveImage: File already exists " . $obj->webpDest);
        if (fileIsAnValidImage($obj->webpDest)) {
            _error_log("ReceiveImage: file is not an error image " . filesize($obj->webpDest));
        }
    }
}

if (!empty($obj->jpgDest)) {
    $obj->jpgDest_deleteInvalidImage = deleteInvalidImage(@$obj->jpgDest);
}
if (!empty($obj->jpgSpectrumDest)) {
    $obj->jpgSpectrumDest_deleteInvalidImage = deleteInvalidImage(@$obj->jpgSpectrumDest);
}
if (!empty($obj->gifDest)) {
    $obj->gifDest_deleteInvalidImage = deleteInvalidImage(@$obj->jpgSpegifDestctrumDest);
}
if (!empty($obj->webpDest)) {
    $obj->webpDest_deleteInvalidImage = deleteInvalidImage(@$obj->webpDest);
}

if (!empty($_REQUEST['duration'])) {
    _error_log("ReceiveImage: duration NOT empty {$_REQUEST['duration']}");
    $duration = $video->getDuration();
    if (empty($duration) || $duration === 'EE:EE:EE') {
        _error_log("ReceiveImage: duration Line " . __LINE__);
        $video->setDuration($_REQUEST['duration']);
    } else if ($_REQUEST['duration'] !== 'EE:EE:EE') {
        _error_log("ReceiveImage: duration Line " . __LINE__);
        $video->setDuration($_REQUEST['duration']);
    }
} else {
    _error_log("ReceiveImage: duration was empty {$_REQUEST['duration']}");
}

$videos_id = $video->save();
Video::clearCache($videos_id, true);
AVideoPlugin::onEncoderReceiveImage($videos_id);

$obj->error = false;
$obj->video_id = $videos_id;
$v = new Video('', '', $videos_id, true);
$obj->video_id_hash = $v->getVideoIdHash();
$obj->releaseDate = @$_REQUEST['releaseDate'];
$obj->releaseTime = @$_REQUEST['releaseTime'];

$json = json_encode($obj);
_error_log("ReceiveImage: Files Received for video {$videos_id}: " . $video->getTitle() . " {$json}");
die($json);

/*
_error_log(json_encode($_REQUEST));
_error_log(json_encode($_FILES));
var_dump($_REQUEST, $_FILES);
*/
