<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';

if (User::isLogged()) {
    redirectIfRedirectUriIsSet();
}
//$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
//$advancedCustom = _json_decode($json_file);
$_page = new Page(array('My Account'));

if (User::isLogged()) {
    $_page->setExtraScripts(
        array(
            'node_modules/croppie/croppie.min.js',
        )
    );
    $_page->setExtraStyles(
        array(
            'node_modules/croppie/croppie.css',
            'view/css/bodyFadein.css'
        )
    );
}
$_page->setIncludeInHead(Array('view/bootstrap/fileinput.php'));

$inc = array();
$inc[] = 'view/container_fluid_header.php';
if (User::isLogged()) {
    $inc[] = 'view/userBody.php';
} else {
    $inc[] = 'view/userLogin.php';
}
$inc[] = 'view/container_fluid_footer.php';
$_page->setIncludeInBody($inc);

$_page->print();
?>
