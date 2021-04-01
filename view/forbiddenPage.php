<?php
require_once dirname(__FILE__) . '/../videos/configuration.php';
$config = new Configuration();
$global['isForbidden'] = true;
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Forbidden") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        CustomizeUser::autoIncludeBGAnimationFile();
        ?>
        <div class="container">
            <?php
            include $global['systemRootPath'] . 'view/img/image403.php';
            ?>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>  
    </body>
</html>
