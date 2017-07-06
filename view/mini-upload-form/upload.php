<?php
$configFile = dirname(__FILE__).'/../../videos/configuration.php';
require_once $configFile;

/*
 *  Run the conversion script in the background when called through the web interface (the default)
 *  For the CLI it is better to run it in the foreground to avoid swamping the system with conversion
 *  processes during batch conversions
 */
$background = "&";

require_once $global['systemRootPath'] . 'objects/functions.php';

if (isCommandLineInterface()) {
    $opts  = "";
    $opts .= "u:";  // user
    $opts .= "f:";  // file
    $opts .= "d:";  // description
    $opts .= "g:";  // comma-separated groups (numerical IDs) for which this video should be accessible - video will be public if left empty
    $opts .= "c";   // copy original file (instead of move/rename)
    $opts .= "b";   // run conversion script in background
    $opts .= "h";   // show help message and exit

    $longopts = [];

    $options = getopt($opts, $longopts);

    if (array_key_exists('h', $options)) {
        print <<<'EOT'

Use: php upload.php -u <user> -f <file> [-d <description>] [-g "group1[,group2]] [-c] [-h]

    -u user     valid username
    -f file     input filename
    -d descr    description
    -g group(s) comma-separated numerical groups for which this item will be visible,
                item will be public if this is left out
    -c          copy original to destination directory (file will be moved if this is left out)
    -h          this help message


EOT;
        exit;
    }

    /*
     * login user without password
     */
    $user = new User(false, $options['u'],false);
    $user->login(true);

    /*
     * populate $_FILES to emulate POSTed file
     */
    $_FILES['upl']['name'] = basename($options['f']);
    $_FILES['upl']['error'] = (is_readable($options['f'])) ? 0 : 1;
    $_FILES['upl']['tmp_name'] = $options['f'];
    $_FILES['upl']['size'] = filesize($options['f']);

    if (!empty($options['d'])) {
        $_FILES['upl']['description'] = $options['d'];
    }
    
    if (!empty($options['g'])) {
        $_FILES['upl']['videoGroups'] = explode(',', $options['g']);
    }
    
    if (array_key_exists('c', $options)) {
        $_FILES['upl']['copyOriginalFile'] = true;
    }

    if (!array_key_exists('b', $options)) {
        $background="";
    }

} else {
    header('Content-Type: application/json');
}

if (!User::canUpload()) {
    //die('{"status":"error", "msg":"Only logged users can upload"}');
    croak(["status" => "error"
        , "msg" => "Only logged users can upload"]);
}
//echo "Success: login OK\n";

//if (!isCommandLineInterface()) {
//    header('Content-Type: application/json');
//}

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
    $type = "";
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
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", $path_parts['filename']);
    $filename = uniqid($mainName . "_", true);

    $video = new Video(preg_replace("/_+/", " ", $_FILES['upl']['name']), $filename, @$_FILES['upl']['videoId']);
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
    } else if (array_key_exists('dontMoveUploadedFile', $_FILES['upl'])) {
        if (!rename($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
            die("Error on rename(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")");
        }
    } else if (!move_uploaded_file($_FILES['upl']['tmp_name'], "{$global['systemRootPath']}videos/original_" . $filename)) {
        die("Error on move_uploaded_file(" . $_FILES['upl']['tmp_name'] . ", " . "{$global['systemRootPath']}videos/original_" . $filename . ")");
    }

    $cmd = PHP_BINDIR."/php -f {$global['systemRootPath']}view/mini-upload-form/videoEncoder.php {$filename} {$id} {$type} > /dev/null 2>/dev/null {$background}";
    //echo "** executing command {$cmd}\n";
    exec($cmd);

    //exec("/usr/bin/php -f videoEncoder.php {$_FILES['upl']['tmp_name']} {$filename}  1> {$global['systemRootPath']}videos/{$filename}_progress.txt  2>&1", $output, $return_val);
    //var_dump($output, $return_val);

    //echo '{"status":"success", "msg":"Your video (' . $filename . ') is encoding <br> ' . $cmd . '", "filename":"' . $filename . '", "duration":"' . $duration . '"}';
    status(["status" => "success"
        , "msg" => "Your video ($filename) is encoding \n $cmd"
        , "filename" => "$filename", "duration" => "$duration"]);
    exit;
}

//echo '{"status":"error", "msg":' . json_encode($_FILES) . ', "type":"$_FILES Error"}';
status(["status" => "error", "msg" => print_r($_FILES,true), "type" => '$_FILES Error']);
exit;
