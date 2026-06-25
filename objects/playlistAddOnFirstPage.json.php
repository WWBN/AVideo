<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
enforceRateLimit('playlist_show_on_first_page', 30, 60);
if (!PlayLists::canManageAllPlaylists()) {
    forbiddenPage('Permission denied', true);
}
if (empty($_REQUEST['playlist_id'])) {
    forbiddenPage('playlist_id is empty', true);
}

$obj = new stdClass();
$obj->playlist_id = $_REQUEST['playlist_id'];
$obj->showOnFirstPage = $_REQUEST['showOnFirstPage'];
$obj->msg = '';

$pl = new PlayList($_POST['playlist_id']);
$pl->setShowOnFirstPage($_REQUEST['showOnFirstPage']);
$obj->saved = $pl->save();
$obj->error = empty($obj->saved);

echo json_encode($obj);
