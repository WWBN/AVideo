<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/CloneSite/Objects/Clones.php';
header('Content-Type: application/json');

// Security: only admins may list clone keys (they contain authentication credentials).
if (!User::isAdmin()) {
    http_response_code(403);
    die(json_encode(['error' => true, 'msg' => 'Admin required']));
}

$rows = Clones::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}
