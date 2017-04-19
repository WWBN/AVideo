<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

$filename = $argv[1];
$videoURL = $argv[2];
$userId = $argv[3];


$filenameTemp = uniqid("{$filename}_", true).".mp4";
$dir = "{$global['systemRootPath']}videos/downloaded/";
if (!file_exists($dir)) {
    $cmd = "mkdir {$dir} && chmod 777 {$dir}";
    exec($cmd . "  1> {$dir}{$filenameTemp}_downloadProgress.txt  2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "{$cmd}\\n ** command ERROR**\n", print_r($output, true);
    } 
    //mkdir($dir, 0777, true);
}

$cmd = "youtube-dl -o {$dir}{$filenameTemp} {$videoURL} -k";
exec($cmd . "  1> {$dir}{$filenameTemp}_downloadProgress.txt  2>&1", $output, $return_val);
if ($return_val !== 0) {
    echo "{$cmd}\\n **youtube-dl get video ERROR**\n", print_r($output, true);
} else {
    // save and encode video
    $_FILES['upl'] = array();
    $_FILES['upl']['error'] = 0;
    $_FILES['upl']['name'] = $filename.".mp4";
    $_FILES['upl']['tmp_name'] = "{$dir}{$filenameTemp}";
    $user = new User($userId);
    $user->login(true);
    require "{$global['systemRootPath']}view/mini-upload-form/upload.php";
}

