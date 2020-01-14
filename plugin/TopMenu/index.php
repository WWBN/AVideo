<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/MenuItem.php';
global $config;

$menuItem = new MenuItem($_GET['id']);
$url = $menuItem->getUrl();
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
    <?php
    if (!empty($url) && strpos($url, 'iframe:') !== false) {
        $url = str_replace("iframe:", "", $url);
        ?>
        <body class="<?php echo $global['bodyClass']; ?>" style="margin:0px;overflow:hidden">
            <?php
            include $global['systemRootPath'] . 'view/include/navbar.php';
            ?>
            <iframe src="<?php echo $url; ?>" frameborder="0" style="height:100%;width:100%" height="100%" width="100%"></iframe>
            
            <?php
            include $global['systemRootPath'] . 'view/include/footer.php';
            ?>
        </body>          
        <?php
    } else {
        ?>
        <body class="<?php echo $global['bodyClass']; ?>">
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
        <?php
    }
    ?>
</html>
