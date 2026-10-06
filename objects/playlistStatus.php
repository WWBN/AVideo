<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
// filename does not end in .json.php, so autoCSRFGuard() never runs for this endpoint
forbidIfIsUntrustedRequest('playlistStatus');
enforceRateLimit('playlist_status_change', 80, 60);
if (!User::isLogged()) {
    forbiddenPage('Permission denied', true);
}
$obj = new PlayList($_POST['playlist_id']);
if (User::getId() !== $obj->getUsers_id()) {
    forbiddenPage('Permission denied', true);
}
$obj->setStatus($_POST['status']);
echo '{"status":"'.$obj->save().'"}';
