<?php
require_once '../videos/configuration.php';
require_once '../plugin/YouPHPTubePlugin.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("Help"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <h1>User-manual of <?php echo $config->getWebSiteTitle(); ?></h1>
            <p>Here you can find help, how this plattform works.</p>
            <?php 
                echo YouPHPTubePlugin::getHelp();
            ?>

        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

        <script>
            $(document).ready(function () {



            });

        </script>
    </body>
</html>
