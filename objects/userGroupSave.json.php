<?php
error_reporting(0);
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';

require_once $global['systemRootPath'] . 'objects/userGroups.php';
if (!is_array($_POST['id'])) {
    $_POST['id'] = [$_POST['id']];
}
$obj->videos_ids = $_POST['id'];
$obj->users_groups_id = $_POST['users_groups_id'];
$obj->add = $_POST['add'];

foreach ($obj->videos_ids as $videos_id) {
    if (!Video::canEdit($videos_id)) {
        forbiddenPage('You can not Manage This Video');
    }
    if (!empty($obj->add)) {
        $obj->addResponse = UserGroups::addVideoGroups($videos_id, $obj->users_groups_id);
        $obj->error = empty($obj->addResponse);
    } else {
        $obj->delResponse = UserGroups::deleteVideoGroups($videos_id, $obj->users_groups_id);
        $obj->error = empty($obj->delResponse);
    }

}
die(json_encode($obj));
