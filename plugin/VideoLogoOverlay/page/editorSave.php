<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin logo overlay"));
    exit;
}
header('Content-Type: application/json');
require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';

$plugin = new VideoLogoOverlay();

$obj = new stdClass();

$o = $plugin->getDataObject();
$o->position = $_POST['position'];
$o->opacity = $_POST['opacity'];
$o->url = $_POST['url'];

$fileData = base64DataToImage($_POST['logoImgBase64']);
$fileName = 'logoOverlay.png';
$photoPath = $global['systemRootPath'] . '/videos/' . $fileName;
$obj->bytes = file_put_contents($photoPath, $fileData);

$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($o));
$obj->saved = $p->save();

echo json_encode($obj);
?>