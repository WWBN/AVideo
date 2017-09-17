<?php
require_once './plugin.php';
header('Content-Type: application/json');
$row = Plugin::getAvailablePlugins();
$total = count($row);
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($row).'}';