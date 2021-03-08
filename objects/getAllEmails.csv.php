<?php

global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if(!User::isAdmin()){
    die("Admin only");
}

$users = array();
$sql = "SELECT id, user, name, email, status, created, isAdmin FROM users";
if ($result = $global['mysqli']->query($sql)) {
    while ($p = $result->fetch_assoc()) {
        $groups = UserGroups::getUserGroups($p['id']);
        $groupsName = array();
        foreach ($groups as $value) {
            $groupsName[] = $value['group_name'];
        }
        $p['groups'] = implode(', ', $groupsName);
        $users[] = $p;
    }
}
$output = fopen("php://output", 'w') or die("Can't open php://output");
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename=email.csv");
fputcsv($output, array('id', 'user', 'name', 'email', 'status', 'created', 'isAdmin', 'Groups'));
foreach ($users as $user) {
    fputcsv($output, $user);
}
fclose($output) or die("Can't close php://output");
