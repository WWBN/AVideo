<?php
//error_reporting(0);
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload()) {
    die('{"error":"'.__("Permission denied").'"}');
}

$msg = "";
$info = $infoObj = "";
require_once 'video.php';

if(!empty($_POST['id'])){
    if(!Video::canEdit($_POST['id'])){
        die('{"error":"'.__("Permission denied").'"}');
    }
}

$obj = new Video($_POST['title'], "", @$_POST['id']);
$obj->setClean_Title($_POST['clean_title']);
if(!empty($_POST['videoLink'])){    
    //var_dump($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));exit;
    $path_parts = pathinfo($_POST['videoLink']);
    if(empty($_POST['id']) && empty($path_parts["extension"]) ){
        $info = url_get_contents($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));
        $infoObj = json_decode($info);
        $filename = uniqid("_YPTuniqid_", true);
        $obj->setFilename($filename);
        $obj->setTitle($infoObj->title);
        $obj->setClean_title($infoObj->title);
        $obj->setDuration($infoObj->duration);
        $obj->setDescription($infoObj->description);
        file_put_contents($global['systemRootPath'] . "videos/{$filename}.jpg", base64_decode($infoObj->thumbs64));
    }else{
        $filename = uniqid("_YPTuniqid_", true);
        $obj->setFilename($filename);
        $obj->setTitle($path_parts["filename"]);
        $obj->setClean_title($path_parts["filename"]);
        $obj->setDuration("");
        $obj->setDescription(@$_POST['description']);
    }
    $obj->setVideoLink($_POST['videoLink']);
    
    if(!empty($path_parts["extension"])){
        $audioLinks = array('mp3', 'ogg');
        if(in_array(strtolower($path_parts["extension"]), $audioLinks)){
            $obj->setType('linkAudio');
        }else{
            $obj->setType('linkVideo');
        }
    }else{
        $obj->setType('embed');
    }
    
    if(!empty($_POST['videoLinkType'])){ 
        $obj->setType($_POST['videoLinkType']);
    }
    $obj->setStatus('a');
}
$obj->setNext_videos_id($_POST['next_videos_id']);
if(!empty($_POST['description'])){
    $obj->setDescription($_POST['description']);
}
if (empty($advancedCustom->userCanNotChangeCategory) || User::isAdmin()) {
    $obj->setCategories_id($_POST['categories_id']);
}
$obj->setVideoGroups(empty($_POST['videoGroups'])?array():$_POST['videoGroups']);

if(User::isAdmin()){
    $obj->setUsers_id($_POST['users_id']);
}

$resp = $obj->save(true);

echo '{"status":"'.!empty($resp).'", "msg": "'.$msg.'", "info":'. json_encode($info).', "infoObj":'. json_encode($infoObj).'}';
