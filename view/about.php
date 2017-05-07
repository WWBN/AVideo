<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("About"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <div class="bgWhite">
                <div class="jumbotron">
                <h1><?php echo __("About Sfrancis.ca!"); ?></h1>
                <?php echo __("<p>This website has both a private and public collection of videos discussing the latest information technology, accounting procedures and tax regulations applicable to businesses.  Authorised users can upload, view, share and comment on videos. We are currently on beta mode and the final version will be released shortly. We hope you enjoy the useful content available on this site and please feel free to give us your input and sugestions. </p>"); ?>
                </div>
                <div class="alert alert-success"><?php printf(__("Business - Information Technology, Accounting & Tax"), $config->getVersion()); ?></div>
            </div>

        </div><!--/.container-->
            <?php
            include 'include/footer.php';
            ?>

        <script>
            $(document).ready(function () {



            });

        </script>
    </body>
</html>
