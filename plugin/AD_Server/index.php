<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: VAST</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'plugin/AD_Server/index_head.php';
        ?>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        include $global['systemRootPath'] . 'plugin/AD_Server/index_body.php';
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
