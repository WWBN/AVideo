<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';

if (empty($_GET['file'])) {
    _error_log("XSENDFILE GET file not found ");
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";

if($file=="configuration.php"){
    _error_log("XSENDFILE Cant read this configuration ");
    die("Cant read this");
}

if (file_exists($path)) {
    if (!empty($_GET['download'])) {
        if(!empty($_GET['title'])){
            $quoted = sprintf('"%s"', addcslashes(basename($_GET['title']), '"\\'));
        }else{
            $quoted = sprintf('"%s"', addcslashes(basename($_GET['file']), '"\\'));
        }
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    }
    if(preg_match("/.(mp4|webm|m3u8|mp3|ogg)/i", $path_parts['extension'])){
        if(empty($_GET['ignoreXsendfilePreVideoPlay'])){
            AVideoPlugin::xsendfilePreVideoPlay();
        }
        if (empty($advancedCustom->doNotUseXsendFile)) {
            //_error_log("X-Sendfile: {$path}");
            header("X-Sendfile: {$path}");
        }
    }else{
        $advancedCustom->doNotUseXsendFile = true;
    }
    header("Content-type: " . mime_content_type($path));
    header('Content-Length: ' . filesize($path));
    if (!empty($advancedCustom->doNotUseXsendFile)) {
        //echo url_get_contents($path);
        // stream the file
        $fp = fopen($path, 'rb');
        fpassthru($fp);
    }
    die();
}else{
    _error_log("XSENDFILE ERROR: Not exists {$path}");
}
