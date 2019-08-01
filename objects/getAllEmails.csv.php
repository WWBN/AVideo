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
        $users[] = $p;
    }
}
$output = fopen("php://output", 'w') or die("Can't open php://output");
header("Content-Type:application/csv");
header("Content-Disposition:attachment;filename=email.csv");
fputcsv($output, array('id', 'user', 'name', 'email', 'status', 'created', 'isAdmin'));
foreach ($users as $user) {    
    fputcsv($output, $user);
}
fclose($output) or die("Can't close php://output");
