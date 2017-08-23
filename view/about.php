<?php
require_once '../videos/configuration.php';
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
                    <h1><?php echo __("About YouPHPTube!"); ?></h1>
                    <?php echo __("<p>YouPHPTube! is an video-sharing website, The service was created by Daniel Neto in march 2017. </p><p>The software allow you to upload, view, share and comment on videos, and it makes use of WebM and H.264/MPEG-4 AVC to display a wide variety of user-generated and corporate media videos. </p><p>Best of all, YouPHPTube! is an open source solution that is freely available to everyone.</p>"); ?>
                </div>
                <div class="alert alert-success">
                    <?php printf(__("You are running YouPHPTube version %s!"), $config->getVersion()); ?>
                </div>
                <blockquote class="blockquote">
                    <p class="mb-0"><?php echo __("For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen."); ?></p>
                    <footer class="blockquote-footer">Apostle Paul in <cite title="Source Title">Romans 11:36</cite></footer>
                </blockquote>
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
