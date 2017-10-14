<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$t = LiveTransmition::getValidPlayURL($_GET['playURL']);
if ($t){
   header('location: '.$t);
}else{
    die("Not allow to play this stream on {$_SERVER['HTTP_HOST']}");
}
?>