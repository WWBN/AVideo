<?php
error_reporting(0);
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
    if(empty($_POST['id'])){
        $info = url_get_contents($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));
        $infoObj = json_decode($info);
        $filename = uniqid("_YPTuniqid_", true);
        $obj->setFilename($filename);
        $obj->setTitle($infoObj->title);
        $obj->setClean_title($infoObj->title);
        $obj->setDuration($infoObj->duration);
        $obj->setDescription($infoObj->description);
        file_put_contents($global['systemRootPath'] . "videos/{$filename}.jpg", base64_decode($infoObj->thumbs64));
    }
    $obj->setVideoLink($_POST['videoLink']);
    $obj->setType('embed');
    if(!empty($_POST['videoLinkType'])){ 
        $obj->setType($_POST['videoLinkType']);
    }
    $obj->setStatus('a');
}
$obj->setNext_videos_id($_POST['next_videos_id']);
if(!empty($_POST['description'])){
    $obj->setDescription($_POST['description']);
}
$obj->setCategories_id($_POST['categories_id']);
$obj->setVideoGroups(empty($_POST['videoGroups'])?array():$_POST['videoGroups']);
$resp = $obj->save(true);

echo '{"status":"'.!empty($resp).'", "msg": "'.$msg.'", "info":'. json_encode($info).', "infoObj":'. json_encode($infoObj).'}';
