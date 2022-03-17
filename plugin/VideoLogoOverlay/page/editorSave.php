<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/plugin.php';
header('Content-Type: application/json');
if (!User::isAdmin()) {
    forbiddenPage(__("You can not manager plugin logo overlay"));
}
require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';

$plugin = new VideoLogoOverlay();

$obj = new stdClass();

$o = $plugin->getDataObject();
$o->position->value = $_POST['position'];
$o->opacity = $_POST['opacity'];
$o->url = $_POST['url'];
$o->error = true;

$fileName = 'logoOverlay.png';
$photoPath = $global['systemRootPath'] . '/videos/' . $fileName;
$obj->bytes = saveCroppieImage($photoPath, "image");

$o->error = empty($obj->bytes);

$p = new Plugin(0);
$p->loadFromUUID($plugin->getUUID());
$p->setObject_data(json_encode($o));
$obj->saved = $p->save();

echo json_encode($obj);
?>