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
            <p>Only you can see this, because you are a admin.</p>
            <h3>Settings and plugins</h3>
            <p>The default <a href='<?php echo $global['webSiteRootURL']; ?>siteConfigurations'>site config</a>, you can find on the menu-point. But there are more settings avaible; go to the <a href='<?php echo $global['webSiteRootURL']; ?>plugins'>plugins</a> and check the "CustomiseAdvanced"-Plugin.</p>
            <p>Like on a lot of plugins, on the right site, you will find a button "Edit parameters". This button is always a click worth.</p>
            <p>Also, when you activate a plugin and you see a button "Install Tables", press it at least once, if you never press it, this can cause bugs!</p>
            <h3>Update via git</h3>
            <p>This project is in a fast development. If you have done your setup via git (like in the howto's), you can update very easy!</p>
            <p>In the shell, go to the youphptube-folder and type "git pull" there. Or, for copy-paste: "cd <?php echo $global['systemRootPath']; ?>; git pull" . </p>
            <p>It can be, that you will need a database-update after. For this, go as admin to the menu-point "<a href='<?php echo $global['webSiteRootURL']; ?>update'>Update version</a>".</p>
            <p>Done!</p>
            <h3>Issues on github</h3>
            <p>If you want to tell us, what is not working for you, this is great and helps us, to make the software more stable.</p>
            <p>Some information can help us, to find your problem faster:</p> <ul><li>Content of <a href='<?php echo $global['webSiteRootURL']; ?>videos/youphptube.log'>videos/youphptube.log</a></li><li>Content of <a href='<?php echo $global['webSiteRootURL']; ?>videos/youphptube.js.log'>videos/youphptube.js.log</a></li><li>If public: your domain, so we can try it</li></ul>
            <p>If you can, clear the log-files, reproduce the error and send them. This helps to reduce old or repeating information.</p>
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
