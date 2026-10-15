<?php
$sessionName = 'themeCSSSession';
require_once dirname(__DIR__) . '/objects/functionsPHP.php';
_setSessionCookieParams(0, _getCookieRequestDomain());
session_name($sessionName);
session_start();
if(!empty($_SESSION['theme'])){
    $theme = $_SESSION['theme'];
    $doNotStartSessionIncludeConfig = $doNotConnectDatabaseIncludeConfig = 1;
}
session_write_close();
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header("Content-type: text/css; charset: UTF-8");
if(empty($theme)){
    $theme = getCurrentTheme();
    _mysql_close();
    _session_write_close();
    session_name($sessionName);
    session_start();
    $_SESSION['theme'] = $theme;
    _session_write_close();
    echo "/* theme = {$theme} from DB */".PHP_EOL;
}else{    
    echo "/* theme = {$theme} from Session */".PHP_EOL;
}
$themeFile = "{$global['systemRootPath']}view/css/custom/{$theme}.css";
if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $theme) || !file_exists($themeFile)) {
    $theme = 'default';
    $themeFile = "{$global['systemRootPath']}view/css/custom/{$theme}.css";
}
// Import the theme instead of inlining it, so its relative url()s (local fonts, glyphicons)
// resolve against view/css/custom/ and not against this script's location
echo '@import url("css/custom/' . $theme . '.css?cache=' . filemtime($themeFile) . '");' . PHP_EOL;
exit;
/*
$filename = "{$global['systemRootPath']}videos/cache/custom.css";
if(file_exists($filename)){
    echo file_get_contents($filename);
}
 *
 */
