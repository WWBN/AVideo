<?php
require_once './plugin.php';
header('Content-Type: application/json');
$row = Plugin::getAvailablePlugins();
$total = count($row);
echo '{  "current": 1,"rowCount": '.$total.', "total": '.$total.', "rows":'. json_encode($row).'}';