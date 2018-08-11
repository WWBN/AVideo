<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';
header('Content-Type: application/json');

if(empty($_GET['country']) || empty($_GET['region'])){
    $regions = array();
}else{
    $regions = IP2Location::getCities($_GET['country'], $_GET['region']);
}

echo json_encode($regions);