<?php
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header("Content-type: text/css; charset: UTF-8");
$theme = getCurrentTheme();
_mysql_close();
session_write_close();
echo "/* theme = {$theme} */".PHP_EOL;
echo file_get_contents("{$global['systemRootPath']}view/css/custom/{$theme}.css");
exit;
/*
$filename = "{$global['systemRootPath']}videos/cache/custom.css";
if(file_exists($filename)){
    echo file_get_contents($filename);
}
 * 
 */