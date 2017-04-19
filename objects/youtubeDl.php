<?php

require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();

$filename = $argv[1];
$videoURL = $argv[2];
$userId = $argv[3];

$dir = "{$global['systemRootPath']}videos/downloaded/";
if (!file_exists($dir)) {
    echo "Warning: Directory ({$dir}) Not Found, creating...\n";
    $cmd = "mkdir {$dir} && chmod 777 {$dir}";
    exec($cmd . "  1> {$dir}{$filename}_downloadProgress.txt  2>&1", $output, $return_val);
    if ($return_val !== 0) {
        echo "{$cmd}\\n ** command ERROR we could not create dir **\n", print_r($output, true);
    } else{
        echo "Success: Directory ({$dir}) created\n";
    }
    //mkdir($dir, 0777, true);
}else{
    echo "Success: Directory ({$dir}) Found\n";
}
//$cmd = "youtube-dl -o {$dir}{$filename} {$videoURL} -k";
$cmd = "youtube-dl -o {$dir}{$filename} -f 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4' {$videoURL}";

echo "** executing command {$cmd}\n";
exec($cmd . "  1> {$dir}{$filename}_downloadProgress.txt  2>&1", $output, $return_val);
if ($return_val !== 0) {
    echo "{$cmd}\\n **youtube-dl get video ERROR**\n", print_r($output, true);
} else {
    // save and encode video
    $_FILES['upl'] = array();
    $_FILES['upl']['error'] = 0;
    $_FILES['upl']['name'] = $filename;
    $_FILES['upl']['tmp_name'] = "{$dir}{$filename}";
    $_FILES['upl']['dontMoveUploadedFile'] = true;
    
    require_once $global['systemRootPath'] . 'objects/video.php';
    $video = new Video($filename.__(" (Do not change the title yet, wait to finish download)"), $filename);
    $video->setType("video");
    $video->setStatus('d');
    $_FILES['upl']['videoId'] = $video->save();
    
    $user = new User($userId);
    $user->login(true);
    echo "Success: We got the video, calling the upload action ".print_r($_FILES, true)."\n";
    require "{$global['systemRootPath']}view/mini-upload-form/upload.php";
}

