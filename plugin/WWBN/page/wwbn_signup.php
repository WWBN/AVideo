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
        <title><?php echo $config->getWebSiteTitle(); ?>  :: WWBN</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid"> 
            <iframe src="https://dev4.wwbn.com/platform/?section=signup" style="width: 100%; height: calc(100vh - 60px);"></iframe>
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
