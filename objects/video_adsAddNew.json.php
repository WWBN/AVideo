<?php
header('Content-Type: application/json');
if (empty($global['systemRootPath'])) {
    $global['systemRootPath'] = '../';
}
require_once $global['systemRootPath'] . 'videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin() || empty($_POST['id'])) {
    die('{"error":"'.__("Permission denied").'"}');
}
require 'video_ad.php';
$va = new Video_ad('', '', '', '', $_POST['id']);
$va->setAd_title($_POST["title"]);
$va->setStarts($_POST["starts"]);
$va->setFinish($_POST["finish"]);
$va->setRedirect($_POST["redirect"]);
$va->setSkip_after_seconds($_POST["skipSeconds"]);
$va->setFinish_max_clicks($_POST["clicks"]);
$va->setFinish_max_prints($_POST["prints"]);
$va->setCategories_id($_POST["categories_id"]);
$resp = $va->save();
echo '{"status":"'.!empty($resp).'"}';
