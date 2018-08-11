<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/User_Location/Objects/IP2Location.php';
header('Content-Type: application/json');

if(empty($_GET['country'])){
    $regions = array();
}else{
    $regions = IP2Location::getRegions($_GET['country']);
}

echo json_encode($regions);