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
                <h1><?php echo __("About Sajiv Francis!"); ?></h1>
                <?php echo __("<p>A result-driven business/IT professional with in depth knowledge of GAAP and exposure to IFRS with varied experience in applying ERP solutions to ensure optimal integration and efficient information availability. Expertise in applying project planning and design including vision, blue print, configuration, data migration, unit & integration testing, end-user training. Efficient in analyzing business requirements, needs, objectives and mapping them to business processes.</p>"); ?>
                <h1><?php echo __("About Daniel Neto!"); ?></h1>
                <?php echo __("<p>The developer behind the code that runs this site is Daniel Neto. Daniel comes from Brazil and has been developing PHP based software, one of his awesome creations include YouPHPTube. As a software  professional, his positive  traits include precise attention to detail, ensuring coding requirements exceed expectations, providing a friendly result oriented approach to projects - agile environment. Daniel's experience as a programmer would be an added value in any project setting, he is currently developing YouPHPTube and multiple other programs simultaneously.</p>"); ?>
                </div>
                <div class="alert alert-success"><?php printf(__("Version %s!"), $config->getVersion()); ?></div>
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
