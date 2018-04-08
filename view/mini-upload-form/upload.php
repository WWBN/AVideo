<?php
$configFile = '../../videos/configuration.php';

if (!file_exists($configFile))
{
    $configFile = '../videos/configuration.php';
}

session_write_close();
$obj = new stdClass();
$obj->error = true;
require_once $configFile;

if (!User::canUpload())
{
    $obj->msg = "Only logged users can upload";
    die(json_encode($obj));
}

header('Content-Type: application/json');

// A list of permitted file extensions

$allowed = array(
    'mp4',
    'ogg',
    'mp3'
);

if (isset($_FILES['upl']) && $_FILES['upl']['error'] == 0)
{
    $extension = pathinfo($_FILES['upl']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension) , $allowed))
    {
        $obj->msg = "File extension error [{$_FILES['upl']['name']}], we allow only (" . implode(",", $allowed) . ")";
        die(json_encode($obj));
    }
    
    require_once $global['systemRootPath'] . 'objects/video.php';
    
    $duration = Video::getDurationFromFile($_FILES['upl']['tmp_name']);
    $path_parts = pathinfo($_FILES['upl']['name']);
    $mainName = preg_replace("/[^A-Za-z0-9]/", "", cleanString($path_parts['filename']));
    $filename = uniqid($mainName . "_", true);
    $video = new Video(substr(preg_replace("/_+/", " ", $_FILES['upl']['name']) , 0, -4) , $filename, @$_FILES['upl']['videoId']);
    $video->setDuration($duration);
    if ($extension == "mp4")
    {
        $video->setType("video");
    }
    else
        if (($extension == "mp3") || ($extension == "ogg"))
        {
            $video->setType("audio");
        }
    
    $advancedCustom = YouPHPTubePlugin::getObjectDataIfEnabled("CustomizeAdvanced");
    if (empty($advancedCustom->makeVideosInactiveAfterEncode))
    {
        
        // set active
        
        $video->setStatus('a');
    }
    else
    {
        $video->setStatus('i');
    }
    
    $id = $video->save();
    /**
     * This is when is using in a non uploaded movie
     */
    $aws_s3 = YouPHPTubePlugin::loadPluginIfEnabled('AWS_S3');
    $tmp_name = $_FILES['upl']['tmp_name'];
    $filenameMP4 = $filename . "." . $extension;
    decideMoveUploadedToVideos($tmp_name, $filenameMP4);
    if ((YouPHPTubePlugin::isEnabled("996c9afb-b90e-40ca-90cb-934856180bb9")) && ($extension == "mp4"))
    {
        require_once $global['systemRootPath'] . 'plugin/MP4ThumbsAndGif/MP4ThumbsAndGif.php';
        
        $videoFileName = $video->getFilename();
        MP4ThumbsAndGif::getImage($videoFileName, 'jpg');
        MP4ThumbsAndGif::getImage($videoFileName, 'gif');
    }
    else
        if ((YouPHPTubePlugin::isEnabled("916c9afb-css90e-26fa-97fd-864856180cc9")) && ($extension == "mp4"))
        {
            require_once $global['systemRootPath'] . 'plugin/MP4ThumbsAndGifLocal/MP4ThumbsAndGifLocal.php';
            
            $videoFileName = $video->getFilename();
            MP4ThumbsAndGifLocal::getImage($videoFileName, 'jpg');
            MP4ThumbsAndGifLocal::getImage($videoFileName, 'gif');
        }
    
    //    } else if(($extension=="mp3")||($extension=="ogg")){
    //  }
    
    $obj->error = false;
    $obj->filename = $filename;
    $obj->duration = $duration;
    die(json_encode($obj));
}

$obj->msg = "\$_FILES Error";
$obj->FILES = $_FILES;
die(json_encode($obj));