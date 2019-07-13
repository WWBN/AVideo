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
} else if (!empty($_POST['itemsToSave'])) {

    foreach ($_POST['itemsToSave'] as $value) {
        //$info = url_get_contents($config->getEncoderURL() . "getLinkInfo/" . base64_encode($value));
        //$infoObj = json_decode($info);
        $filename = uniqid("_YPTuniqid_", true);
        $videos = new Video();
        $videos->setFilename($filename);
        $videos->setTitle($value['title']);
        $videos->setClean_title($value['title']);
        $videos->setDuration($value['duration']);
        $videos->setDescription($value['description']);
        file_put_contents($global['systemRootPath'] . "videos/{$filename}.jpg", $value['thumbs']);
        $videos->setVideoLink($value['link']);
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
