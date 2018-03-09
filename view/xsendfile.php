<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";
if(!empty($_GET['download'])){
    $quoted = sprintf('"%s"', addcslashes(basename($_GET['file']), '"\\'));
    $size   = filesize($file);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $quoted); 
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
}
YouPHPTubePlugin::xsendfilePreVideoPlay();
$advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
if(!empty($advancedCustom->doNotUseXsendFile)){
    header("X-Sendfile: {$path}");
}
if(empty($_GET['download'])){
    header("Content-type: " . mime_content_type($path));
}
header('Content-Length: ' . filesize($path));
if(!empty($advancedCustom->doNotUseXsendFile)){
    echo file_get_contents($path);
}
die();
