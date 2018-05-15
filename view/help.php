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
            <p><?php echo YouPHPTubePlugin::getHelpToc(); ?></p>
            <p>Here you can find help, how this plattform works.</p>
            <?php if(User::isAdmin()){ ?>
            <h2>Admin's manual</h2>
            <h3>Settings and plugins</h3>
            <p>The default site's config, you can find on the menu-point. But there are more settings avaible; go to the plugins and check the "CustomiseAdvanced"-Plugin.</p>
            <p>Like on a lot of plugins, on the right site, you will find a button "Edit parameters". This button is always a click worth.</p>
            <p>Also, when you activate a plugin and you see a button "Install Tables", press it at least once, if you never press it, this can cause bugs!</p>
            <hr />
            <?php } ?>
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
