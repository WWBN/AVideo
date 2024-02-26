<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/VideosStatistics/Objects/Statistics.php';
header('Content-Type: application/json');


$array = array(
    'data'=>array(),
    'draw'=>0,
    'recordsTotal'=>0,
    'recordsFiltered'=>0,
);

if(User::isLogged()){
    
    $rows = Statistics::getAllVideoStatistics(User::getId());
    $total = Statistics::getTotalVideoStatistics(User::getId());

    $array = array(
        'data'=>$rows,
        'draw'=>intval(@$_REQUEST['draw']),
        'recordsTotal'=>$total,
        'recordsFiltered'=>$total,
    );
    
}


echo _json_encode($array);
?>