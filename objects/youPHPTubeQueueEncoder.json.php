<?php
header('Content-Type: application/json');
$obj = new stdClass();
$obj->error = true;
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/video.php';

if (!User::canUpload()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}

// A list of permitted file extensions
$allowed = array('mp4', 'avi', 'mov', 'mkv', 'flv', 'mp3', 'wav', 'm4v', 'webm', 'wmv');

if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0) {

    $updateVideoGroups = false;

    //echo "Success: \$_FILES OK\n";
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);

    if (!in_array(strtolower($extension), $allowed)) {
        //echo '{"status":"error", "msg":"File extension error [' . $_FILES['upl']['name'] . '], we allow only (' . implode(",", $allowed) . ')"}';
        status(["status" => "error"
            , "msg" => "File extension error (" . $_FILES['upl']['name'] . "), we allow only (" . implode(",", $allowed) . ")"]);
        exit;
    }

    //echo "Success: file extension OK\n";

    //chack if is an audio
    $type = "video";
    if (strcasecmp($extension, 'mp3') == 0 || strcasecmp($extension, 'wav') == 0) {
        $type = 'audio';
    }

    //var_dump($extension, $type);exit;

    require_once $global['systemRootPath'] . 'objects/video.php';

    //echo "Starting Get Duration\n";
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);

    // check if can upload video (about time limit storage)
    if(!empty($global['videoStorageLimitMinutes'])){
        $maxDuration = $global['videoStorageLimitMinutes']*60;
        $currentStorageUsage = getSecondsTotalVideosLength();
        $thisFile = parseDurationToSeconds($duration);
        $limitAfterThisFile = $currentStorageUsage+$thisFile;
        if($maxDuration<$limitAfterThisFile){
            status(["status" => "error", "msg" => "Sorry, your storage limit has run out."
                . "<br>[Max Duration: {$maxDuration} Seconds]"
                . "<br>[Current Srotage Usage: {$currentStorageUsage} Seconds]"
                . "<br>[This File Duration: {$thisFile} Seconds]"
                . "<br>[Limit after this file: {$limitAfterThisFile} Seconds]", "type" => '$_FILES Limit Error']);
            if(!empty($_FILES['upl']['videoId'])){
                $video = new Video("", "", $_FILES['upl']['videoId']);
                $video->delete();
            }
            exit;
        }
    }


    $path_parts = pathinfo($_FILES['upl']['name']);
    
    if(empty($path_parts['extension']) || !in_array($path_parts['extension'], $global['allowedExtension'])){
        error_log("Extension not allowed File ".__FILE__.": ".  print_r($path_parts, true));
        die();
    }
    
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($path_parts['filename']));
    $filename = uniqid($mainName . "_YPTuniqid_", true);

    $video = new Video(preg_replace("/_+/", " ", $path_parts['filename']), $filename, @$_FILES['upl']['videoId']);
    $video->setDuration($duration);
    if ($type == 'audio') {
        $video->setType($type);
    } else {
        $video->setType("video");
    }
    $video->setStatus('e');

    /*
     * set visibility for private videos
     */
    if (array_key_exists('videoGroups', $_FILES['upl'])) {
        $video->setVideoGroups($_FILES['upl']['videoGroups']);
        $updateVideoGroups = true;
    }

    /*
     * set description (if given)
     */
    if (!empty($_FILES['upl']['description'])) {
        $video->setDescription($_FILES['upl']['description']);
    }
    /**
     * Make a better title and clean title
     */
    $videoNewTitle = $video->getTitle();
    $titleParts = explode("YPTuniqid", $videoNewTitle);
    $video->setTitle($titleParts[0]);
    $video->setClean_title($titleParts[0]);
    $id = $video->save($updateVideoGroups);

    /**
     * Copy, rename or move original file
     *
     * copy:   used from command line when -c option is included
     * rename: used with files which were downloaded directly into the videos directory (from other media sites)
     * move:   default, used with uploaded files
     */
    if (array_key_exists('copyOriginalFile', $_FILES['upl'])) {
        if (!copy($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
            die("Error on copy(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")");
        }
    } elseif (array_key_exists('dontMoveUploadedFile', $_FILES['upl'])) {
        if (!rename($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
            die("Error on rename(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")");
        }
    } elseif (!move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
        die("Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")");
    }

    $video = new Video('', '', $id);
    // send to encoder
    $queue = array();
    if ($video->getType() == 'video') {
        if ($config->getEncode_mp4()) {
            $queue[] = $video->queue("mp4");
        }
        if ($config->getEncode_webm()) {
            $queue[] = $video->queue("webm");
        }
    } elseif ($config->getEncode_mp3spectrum()) {
        if ($config->getEncode_mp4()) {
            $queue[] = $video->queue("mp4_spectrum");
        }
        if ($config->getEncode_webm()) {
            $queue[] = $video->queue("webm_spectrum");
        }
    } else {
        $queue[] = $video->queue("mp3");
        $queue[] = $video->queue("ogg");
    }

    //exec("/usr/bin/php -f videoEncoder.php {$_FILES['upl']['tmp_name']} {$filename}  1> {$global['systemRootPath']}videos/{$filename}_progress.txt  2>&1", $output, $return_val);
    //var_dump($output, $return_val);

    //echo '{"status":"success", "msg":"Your video (' . $filename . ') is encoding <br> ' . $cmd . '", "filename":"' . $filename . '", "duration":"' . $duration . '"}';
    status(["status" => "success"
        , "msg" => "Your video ($filename) is queue"
        , "filename" => "$filename"
        , "duration" => "$duration"
        , "queue" => json_encode($queue)]);
    exit;
}

//echo '{"status":"error", "msg":' . json_encode($_FILES) . ', "type":"$_FILES Error"}';
status(["status" => "error", "msg" => print_r($_FILES,true), "type" => '$_FILES Error']);
exit;
