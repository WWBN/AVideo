<?php
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

$obj = new stdClass();
$obj->error = true;
$obj->msg = '';
$obj->users_id = User::getId();

if (empty($obj->users_id)) {
    forbiddenPage('You are not logged');
}
if (empty($_REQUEST['birth_date'])) {
    forbiddenPage('You need to inform your birth date');
}

$user = new User(0);
$user->loadSelfUser();
$user->setBirth_date($_REQUEST['birth_date']);
$obj->users_id = $user->save();

$obj->error = empty($obj->users_id);
User::updateSessionInfo();
die(json_encode($obj));