<?php
require_once '../../videos/configuration.php';
header("Content-Type: text/plain");
$content = file_get_contents('avideoLogin.php');
echo str_replace('{webSiteRootURL}', "{$global['webSiteRootURL']}", $content);
?>