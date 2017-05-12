<?php
header('Content-Type: application/json');
if(empty($global['systemRootPath'])){
    $global['systemRootPath'] = "../";
}
require_once $global['systemRootPath'].'videos/configuration.php';
require_once $global['systemRootPath'].'locale/function.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::canUpload() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}

require_once 'video.php';
$obj = new Video($_POST['title'], "", $_POST['id']);
$obj->setClean_Title($_POST['clean_title']);
$obj->setDescription($_POST['description']);
$obj->setCategories_id($_POST['categories_id']);
$obj->setVideoGroups(empty($_POST['videoGroups'])?array():$_POST['videoGroups']);
$resp = $obj->save(true);

// make an ad
if ($resp && User::isAdmin() && !empty($_POST['isAd'])) {
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

echo '{"status":"'.!empty($resp).'"}';