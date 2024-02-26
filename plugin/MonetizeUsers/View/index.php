<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("VideosStatistics");
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        include $global['systemRootPath'] . 'plugin/MonetizeUsers/View/report.php';
        
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
