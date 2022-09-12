<?php
require_once '../videos/configuration.php';

$offlineFile = $global['systemRootPath'] . 'plugin/VideoOffline/offlineVideo.php';
if(file_exists($offlineFile)){
    require_once $offlineFile;
    exit;
}else{
    //forbiddenPage('This feature requires the VideoOffline plugin');
}
?>