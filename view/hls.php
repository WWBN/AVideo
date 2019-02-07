<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

if(empty($_GET['videoDirectory'])){
    die("No directory set");
}

$video = Video::getVideoFromFileName($_GET['videoDirectory']);
$filename =  "{$global['systemRootPath']}videos/{$_GET['videoDirectory']}/index.m3u8";
$_GET['file'] = "{$global['systemRootPath']}videos/{$_GET['videoDirectory']}.m3u8";

$cachedPath = explode("/", $_GET['videoDirectory']);
if(empty($_SESSION['hls'][$cachedPath[0]])){
    YouPHPTubePlugin::xsendfilePreVideoPlay();
    $_SESSION['hls'][$cachedPath[0]] = 1;
}
if(User::canWatchVideo($video['id'])){
    $content = file_get_contents($filename);
    $newContent = str_replace('{$pathToVideo}',  "{$global['webSiteRootURL']}videos/{$_GET['videoDirectory']}/../", $content);
}else{
    $newContent = "Can not see video {$video['id']}";
}
header("Content-Type: text/plain");
echo $newContent;