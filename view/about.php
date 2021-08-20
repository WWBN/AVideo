<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
$metaDescription = "About Page";
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
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php
                    $custom = "";
                    if (AVideoPlugin::isEnabled("c4fe1b83-8f5a-4d1b-b912-172c608bf9e3")) {
                        require_once $global['systemRootPath'] . 'plugin/Customize/Objects/ExtraConfig.php';
                        $ec = new ExtraConfig();
                        $custom = $ec->getAbout();
                    }
                    if (empty($custom)) {
                        ?>
                        <h1><?php echo __("I would humbly like to thank God for giving me the necessary knowledge, motivation, resources and idea to be able to execute this project. Without God's permission this would never be possible."); ?></h1>
                        <blockquote class="blockquote">
                            <h1><?php echo __("For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen."); ?></h1>
                            <footer class="blockquote-footer"><?php echo __("Apostle Paul in"); ?> <cite title="Source Title"><?php echo __("Romans 11:36"); ?></cite></footer>
                        </blockquote>
                        <div class="clearfix"></div>
                        <span class="label label-success"><?php printf(__("You are running AVideo version %s!"), $config->getVersion()); ?></span>

                        <span class="label label-success">
                            <?php printf(__("You can upload max of %s!"), get_max_file_size()); ?>
                        </span>
                        <span class="label label-success">
                            <?php printf(__("You have %s minutes of videos!"), number_format(getSecondsTotalVideosLength() / 6, 2)); ?>
                        </span>
                        <div class="clearfix"></div>
                        <span class="label label-info">
                            <?php echo __("You are using"); ?>: <?php echo get_browser_name() . " " . __("on") . " " . getOS(); ?> (<?php echo isMobile() ? __("Mobile") : __("PC"); ?>)
                        </span>
                        <span class="label label-default">
                            <?php echo $_SERVER['HTTP_USER_AGENT']; ?>
                        </span>
                        

                        <?php
                    } else {
                        echo $custom;
                    }
                    ?>
                </div>
            </div>

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
