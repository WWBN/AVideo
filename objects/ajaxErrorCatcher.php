<?php 
ini_set('error_log', '../videos/youphptube.js.log');
if(!empty($_GET['error'])){
    error_log("[aEC] ".$_GET['error']);
}

?>
