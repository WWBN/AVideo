<?php
$sessionName = 'themeCSSSession';
session_name($sessionName);
session_start();
if(!empty($_SERVER['theme'])){
    $theme = $_SERVER['theme'];
    $doNotConnectDatabaseIncludeConfig = 1;
}
session_write_close();
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header("Content-type: text/css; charset: UTF-8");
if(empty($doNotConnectDatabaseIncludeConfig)){
    $theme = getCurrentTheme();
    _mysql_close();
    _session_write_close();
    session_name($sessionName);
    session_start();
    $_SERVER['theme'] = $theme;
    echo "/* theme = {$theme} from DB */".PHP_EOL;
}else{    
    echo "/* theme = {$theme} from Session */".PHP_EOL;
}
_session_write_close();
echo file_get_contents("{$global['systemRootPath']}view/css/custom/{$theme}.css");
exit;
/*
$filename = "{$global['systemRootPath']}videos/cache/custom.css";
if(file_exists($filename)){
    echo file_get_contents($filename);
}
 *
 */
