<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

$SocketDataObj = AVideoPlugin::getDataObject("YPTSocket");

if($SocketDataObj->socketIO){
    require_once __DIR__.'/server.node.php';
}else{
    require_once __DIR__.'/server.php.php';
}
