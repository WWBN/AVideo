<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once dirname(__FILE__) . '/../../../../videos/configuration.php';
}
if (!User::canStream()) {
    forbiddenPage();
    exit;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Live</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        include $global['systemRootPath'] . 'plugin/Live/view/Live_schedule/panel.php';
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
