<?php
require_once '../../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Permissions/Objects/Users_groups_permissions.php';
header('Content-Type: application/json');

$plugin = AVideoPlugin::loadPluginIfEnabled('Permissions');
if (!User::isAdmin()) {
    die(json_encode(['error' => true, 'msg' => 'You cant do this']));
}

$rows = Users_groups_permissions::getAll();
?>
{"data": <?php echo json_encode($rows); ?>}