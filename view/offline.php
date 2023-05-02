<?php
//require_once '../videos/configuration.php';
$file = dirname(__FILE__) . DIRECTORY_SEPARATOR.'../plugin/VideoOffline/offlineVideo.php';
error_log($file);
$offlineFile = $file;
if(file_exists($offlineFile)){
    require_once $offlineFile;
    exit;
}else{
    //forbiddenPage('This feature requires the VideoOffline plugin');
}
?>