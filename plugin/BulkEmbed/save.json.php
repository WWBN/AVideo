<?php

//error_reporting(0);
header('Content-Type: application/json');
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = array();

if (!User::canUpload()) {
    $obj->msg[] = __("Permission denied");
} else if (!empty($_POST['videoLink'])) {

    if (!is_array($_POST['videoLink'])) {
        $_POST['videoLink'] = array($_POST['videoLink']);
    }
    foreach ($_POST['videoLink'] as $value) {
        $info = url_get_contents($config->getEncoderURL() . "getLinkInfo/" . base64_encode($value));
        $infoObj = json_decode($info);
        $filename = uniqid("_YPTuniqid_", true);
        $videos = new Video();
        $videos->setFilename($filename);
        $videos->setTitle($infoObj->title);
        $videos->setClean_title($infoObj->title);
        $videos->setDuration($infoObj->duration);
        $videos->setDescription($infoObj->description);
        file_put_contents($global['systemRootPath'] . "videos/{$filename}.jpg", base64_decode($infoObj->thumbs64));
        $videos->setVideoLink($value);
        $videos->setType('embed');

        $videos->setStatus('a');

        $resp = $videos->save(true);
        YouPHPTubePlugin::afterNewVideo($resp);

        YouPHPTubePlugin::saveVideosAddNew($_POST, $resp);
        
        $obj->msg[] = $resp;
    }

    $obj->error = false;
}
echo json_encode($obj);
