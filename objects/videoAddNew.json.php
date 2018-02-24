<?php
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
$obj = new Video($_POST['title'], "", @$_POST['id']);
$obj->setClean_Title($_POST['clean_title']);
if(!empty($_POST['videoLink'])){    
    //var_dump($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));exit;
    if(empty($_POST['id'])){
        $info = file_get_contents($config->getEncoderURL()."getLinkInfo/". base64_encode($_POST['videoLink']));
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
    $obj->setStatus('a');
}
$obj->setNext_videos_id($_POST['next_videos_id']);
if(!empty($_POST['description'])){
    $obj->setDescription($_POST['description']);
}
$obj->setCategories_id($_POST['categories_id']);
$obj->setVideoGroups(empty($_POST['videoGroups'])?array():$_POST['videoGroups']);
$resp = $obj->save(true);

// make an ad
if ($resp && User::isAdmin() && !empty($_POST['isAd']) && $_POST['isAd']!=='false') {
    $msg = "Create a ad";
    require 'video_ad.php';
    $va = new Video_ad($_POST['id'], $_POST["adElements"]["categories_id"]);
    $va->setAd_title($_POST["adElements"]["title"]);
    $va->setStarts($_POST["adElements"]["starts"]);
    $va->setFinish($_POST["adElements"]["finish"]);
    $va->setRedirect($_POST["adElements"]["redirect"]);
    $va->setSkip_after_seconds($_POST["adElements"]["skipSeconds"]);
    $va->setFinish_max_clicks($_POST["adElements"]["clicks"]);
    $va->setFinish_max_prints($_POST["adElements"]["prints"]);
    $va->save();
}

echo '{"status":"'.!empty($resp).'", "msg": "'.$msg.'", "info":'. json_encode($info).', "infoObj":'. json_encode($infoObj).'}';
