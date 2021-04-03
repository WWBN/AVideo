<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../../videos/configuration.php';
}
$metaDescription = "Themes Page";
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo __("About") . getSEOComplement() . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="row">

                <?php
                $themes = getThemes();
                foreach ($themes as $value) {
                    ?>
                    <div class=" col-sm-4 col-lg-3">
                        <div class="panel panel-default">
                            <div class="panel-body" style="padding: 5px;">
                                <iframe frameBorder="0" width="100%" height="250px" 
                                        src="<?php echo getCDN(); ?>view/css/custom/theme.php?theme=<?php echo $value; ?>" ></iframe>
                            </div>
                        </div>

                    </div>
                    <?php
                }
                ?>
            </div>

        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
