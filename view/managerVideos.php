<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';


if(!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user?redirectUri={$global['webSiteRootURL']}mvideos");
    exit;
}

if (!User::canUpload(true)) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage videos"));
    exit;
}

if(!empty($_GET['iframe'])){
    $_GET['noNavbar'] = 1;
}

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Audios and Videos") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        include $global['systemRootPath'] . 'view/managerVideos_head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php 
        include $global['systemRootPath'] . 'view/include/navbar.php';
        include $global['systemRootPath'] . 'view/managerVideos_body.php';
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script src="<?php echo getCDN(); ?>view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

    </body>
</html>
