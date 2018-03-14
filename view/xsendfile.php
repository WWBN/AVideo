<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
session_write_close();
require_once $global['systemRootPath'] . 'objects/functions.php';
require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';

if (empty($_GET['file'])) {
    die('GET file not found');
}

function send_video($path){
    if (file_exists($path)){
        $size=filesize($path);
        $fm=@fopen($path,'rb');
        if(!$fm) {
            // You can also redirect here
            header ("HTTP/1.0 404 Not Found");
            die;
        }
        $begin=0;
        $end=$size;
        if(isset($_SERVER['HTTP_RANGE'])) {
            if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i',   
            $_SERVER['HTTP_RANGE'],$matches)){
                $begin=intval($matches[0]);
                if(!empty($matches[1])) {
                    $end=intval($matches[1]);
                }
            }
        }
        if($begin>0||$end<$size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');
        header('Accept-Ranges: bytes');
        header('Content-Length:'.($end-$begin));
        header("Content-Disposition: inline;");
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Transfer-Encoding: binary\n");
        header('Connection: close');
        $cur=$begin;
        fseek($fm,$begin,0);
        while(!feof($fm)&&$cur<$end&&(connection_status()==0)){
            echo fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
            usleep(1000);
        }
        die;
    }
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
if(empty($advancedCustom->doNotUseXsendFile)){
    header("X-Sendfile: {$path}");
}
header('Content-Length: ' . filesize($path));
if(!empty($advancedCustom->doNotUseXsendFile)){
    if(strtolower($path_parts['extension'])==="mp4" || strtolower($path_parts['extension'])==="webm"){
        // Not working yet
        if(!empty($_SERVER['HTTP_RANGE'])){
            send_video($path);
        }else{
            echo file_get_contents($path);
        }
    }else{
        if(empty($_GET['download'])){
            header("Content-type: " . mime_content_type($path));
        }
        echo file_get_contents($path);
    }
}
die();
