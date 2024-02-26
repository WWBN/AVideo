<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user");
    exit;
}


?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("Sign Up") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        CustomizeUser::autoIncludeBGAnimationFile();
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
                <div class="col-xs-12 col-sm-12 col-lg-8">
                    <?php
                    include $global['systemRootPath'] . 'view/userSignUpBody.php';
                    ?>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
            </div>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
