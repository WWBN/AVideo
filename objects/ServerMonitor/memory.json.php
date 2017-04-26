<?php
require_once 'ServerMonitor.php';
header('Content-Type: application/json');
echo json_encode(ServerMonitor::getMemory());

