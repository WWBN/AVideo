<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';
global $config;

$menuItem = new MenuItem($_GET['id']);

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo $menuItem->getTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="bgWhite bg-light clear clearfix">
                <?php echo $menuItem->getText(); ?>
            </div>
        </div>
    <?php
    include $global['systemRootPath'] . 'view/include/footer.php';
    ?>
    <script>
            $(document).ready(function () {
            });
    </script>
</body>
</html>
