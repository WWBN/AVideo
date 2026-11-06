<?php
// Use only the normal startup session. A separate session here (it was "themeCSSSession") left its
// ID in PHP, the main session reused it and the browser's login cookie was replaced, logging the
// user out after opening the Encoder, which loads this file (issue #10674)
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header("Content-type: text/css; charset: UTF-8");
$theme = getCurrentTheme();
_mysql_close();
_session_write_close();
echo "/* theme = {$theme} */".PHP_EOL;
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
