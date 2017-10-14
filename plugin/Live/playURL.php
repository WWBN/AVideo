<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/Live/Objects/LiveTransmition.php';
$t = LiveTransmition::getValidPlayURL($_GET['playURL']);
if ($t){
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Cache-Control: no-store, no-cache,must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0",false);
    header("Pragma: no-cache");
    header('Content-type:application/force-download');
    header('Content-Disposition: attachment; filename=YouPHPTube_Stream');
    header('Content-Length: ' . filesize($t));
    header('Content-Type: '.mime_content_type($t));
   echo file_get_contents($t);
}else{
    die("Not allow to play this stream on {$_SERVER['HTTP_HOST']}");
}
?>