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
                <h1><?php echo __("I would humbly like to thank God for giving me the necessary knowledge, motivation, resources and idea to be able to execute this project. Without God's permission this would never be possible."); ?></h1>
                <blockquote class="blockquote">
                    <h1><?php echo __("For of Him, and through Him, and to Him, are all things: to whom be glory for ever. Amen."); ?></h1>
                    <footer class="blockquote-footer">Apostle Paul in <cite title="Source Title">Romans 11:36</cite></footer>
                </blockquote>
                <div class="btn-group btn-group-justified">
                    <a href="https://www.youphptube.com/" class="btn btn-success">Main Site</a>
                    <a href="https://demo.youphptube.com/" class="btn btn-danger">Demo Site</a>
                    <a href="https://tutorials.youphptube.com/" class="btn btn-primary">Tutorials Site</a>
                    <a href="https://github.com/DanielnetoDotCom/YouPHPTube/issues" class="btn btn-warning">Issues and requests Site</a>
                </div>
                <span class="label label-success"><?php printf(__("You are running YouPHPTube version %s!"), $config->getVersion()); ?></span>

                <span class="label label-success">
                    <?php printf(__("You can upload max of %s!"), get_max_file_size()); ?>
                </span>
                <span class="label label-success">
                    <?php printf(__("You can storage %s minutes of videos!"), (empty($global['videoStorageLimitMinutes'])?"unlimited":$global['videoStorageLimitMinutes'])); ?>
                </span>
                <span class="label label-success">
                    <?php printf(__("You have %s minutes of videos!"), number_format(getSecondsTotalVideosLength()/6, 2)); ?>
                </span>


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
