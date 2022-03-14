<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
$obj = AVideoPlugin::getObjectDataIfEnabled("WWBN");
if(empty($obj)){
    die("Plugin disabled");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("WWBN") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <iframe src="https://wwbn.com/platform/?section=signup&webSiteRootURL=<?php echo urlencode($global['webSiteRootURL']); ?>&token=<?php echo WWBN::getToken(); ?>" style="width: 100%; height: calc(100vh - 60px);" frameBorder="0"></iframe>
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
