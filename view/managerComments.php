<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage comments"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/comment.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo __("Comments"); ?></title>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css" rel="stylesheet" type="text/css"/>
    </head>

    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
                    <?php
        include $global['systemRootPath'] . 'view/include/updateCheck.php';
        ?>
            <?php
            include $global['systemRootPath'] . 'view/videoComments.php';
            ?>

        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
