<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    error_log("XSENDFILE GET file not found ");
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";

if($file=="configuration.php"){
    error_log("XSENDFILE Cant read this configuration ");
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
    if(empty($_GET['ignoreXsendfilePreVideoPlay'])){
        YouPHPTubePlugin::xsendfilePreVideoPlay();
    }
    if (empty($advancedCustom->doNotUseXsendFile)) {
        //error_log("X-Sendfile: {$path}");
        header("X-Sendfile: {$path}");
    }
    if (empty($_GET['download'])) {
        header("Content-type: " . mime_content_type($path));
    }
    header('Content-Length: ' . filesize($path));
    if (!empty($advancedCustom->doNotUseXsendFile)) {
        echo url_get_contents($path);
    }
    die();
}else{
    error_log("XSENDFILE ERROR: Not exists {$path}");
}
