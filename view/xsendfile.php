<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    die('GET file not found');
}

$path_parts = pathinfo($_GET['file']);
$file = $path_parts['basename'];
$path = "{$global['systemRootPath']}videos/{$file}";
YouPHPTubePlugin::xsendfilePreVideoPlay();
header("X-Sendfile: {$path}");
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
}else{
    header("Content-type: " . mime_content_type($path));
}
header('Content-Length: ' . filesize($path));
die();
