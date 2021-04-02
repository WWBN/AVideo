<?php
$isFirstPage = 1;
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/video.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$obj = AVideoPlugin::getObjectData("YouPHPFlix2");
?>
<!DOCTYPE html>
<html>
    <head>
        <script>
            var webSiteRootURL = '<?php echo $global['webSiteRootURL']; ?>';
        </script>
        <?php
        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixHead.php';
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

        <title><?php echo $config->getWebSiteTitle(); ?></title>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>

        <div class="container-fluid nopadding flickity-area" id="mainContainer" style="display:none;">
            <?php
            include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixBody.php';
            ?>
        </div>

        <?php
        include $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlixFooter.php';
        ?>

    </body>
</html>
