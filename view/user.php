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
include $global['systemRootPath'] . 'view/bootstrap/fileinput.php';
?>

<div class="container-fluid">
    <?php
    if (User::isLogged()) {
        include $global['systemRootPath'] . 'view/userBody.php';
    } else {
        include $global['systemRootPath'] . 'view/userLogin.php';
    }

    ?>
</div><!--/.container-->
<?php
$_page->print();
?>