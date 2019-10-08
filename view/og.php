<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <?php 
        getOpenGraph(@$_GET['videos_id']);
        getLdJson(@$_GET['videos_id']);
        ?>
    </head>
    <body>
    </body>
</html>
