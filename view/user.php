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
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("My Account") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo getURL('node_modules/croppie/croppie.css'); ?>" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getURL('node_modules/croppie/croppie.min.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo getURL('view/js/bootstrap-fileinput/css/fileinput.min.css'); ?>" rel="stylesheet" type="text/css"/>
        <script src="<?php echo getURL('view/js/bootstrap-fileinput/js/fileinput.min.js'); ?>" type="text/javascript"></script>
        <link href="<?php echo getURL('view/css/bodyFadein.css'); ?>" rel="stylesheet" type="text/css"/>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid">
            <?php
            if(User::isLogged()){
                include $global['systemRootPath'] . 'view/userBody.php';
            }else{
                include $global['systemRootPath'] . 'view/userLogin.php';
            }

            ?>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
