<?php
require_once 'userGroups.php.php';
header('Content-Type: application/json');
$rows = UserGroups::getAllUsersGroups();
$total = UserGroups::getTotalUsersGroups();
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($categories).'}';
