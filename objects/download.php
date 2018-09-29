<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}

require_once $global['systemRootPath'] . 'objects/video.php';

if(isset($_GET['clean_title']))
$v=Video::getVideoFromCleanTitle($_GET['clean_title']); 
else
$v=false;

if($v)
{
    $video=new video($v['title'], $v['filename'], $v['id']);
    switch($_GET['filetype'])
    {
    
        case "mp4_Low":
            $filetype="_Low.mp4"; 
        break;
        case "mp4_SD":
            $filetype="_SD.mp4"; 
        break;
        case "mp4_HD":
            $filetype="_HD.mp4"; 
        break;
        default:
            $filetype=".".$_GET['filetype'];
        
    }
    $file=$video->getSourceFile($video->getFileName(), $filetype);
    
    header( 'Expires: Mon, 1 Apr 1974 05:00:00 GMT' );
    header( 'Pragma: no-cache' );
    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header( 'Content-Description: File Download' );
    header( 'Content-Type: application/octet-stream' );
    header( 'Content-Length: '.filesize( $file['path'] ) );
    header( 'Content-Disposition: attachment; filename="'.$_GET['clean_title'].$filetype.'"' );
    header( 'Content-Transfer-Encoding: binary' );
    if(file_exists($file['path']))
    readfile( $file['path'] );  
    else
    print ""; 
    exit();    
}

?>
