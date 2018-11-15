<?php
global $global, $config;
if(!isset($global['systemRootPath'])){
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!Category::canCreateCategory()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage categories"));
    exit;
}
 ?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo __("Category"); ?></title>

        <?php 
            include $global['systemRootPath'] . 'view/include/head.php';
            
        include $global['systemRootPath'] . 'view/managerCategories_head.php';
        ?>
    </head>
    <body>
        <?php 
        include $global['systemRootPath'] . 'view/include/navbar.php'; 
        include $global['systemRootPath'] . 'view/managerCategories_body.php';
        include $global['systemRootPath'] . 'view/include/footer.php'; ?>
    </body>
</html>
