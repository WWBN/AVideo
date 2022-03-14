<?php
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("EPG") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid">
            <?php
            $_REQUEST['site'] = get_domain($global['webSiteRootURL']);
            echo '<div class="panel panel-default"><div class="panel-heading">' . __("Now Playing") . '</div><div class="panel-body">';
            //include_once $global['systemRootPath'] . 'plugin/PlayLists/epg.html.php';
            include_once $global['systemRootPath'] . 'plugin/PlayLists/epg.day.php';
            echo '</div></div>';
            ?>
        </div>
            <?php
            include $global['systemRootPath'] . 'view/include/footer.php';
            ?>
        <script>


        </script>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
