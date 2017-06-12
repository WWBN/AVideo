<?php

require_once 'subscribe.php';
header('Content-Type: application/json');
$Subscribes = Subscribe::getAllSubscribes();
$total = Subscribe::getTotalSubscribes();
echo '{  "current": '.$_POST['current'].',"rowCount": '.$_POST['rowCount'].', "total": '.$total.', "rows":'. json_encode($Subscribes).'}';