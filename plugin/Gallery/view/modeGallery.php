<?php
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php
            echo $siteTitle;
            ?></title>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="container-fluid gallery">
            <div class="row text-center" style="padding: 10px;">
                <?php echo getAdsLeaderBoardTop(); ?>
            </div>
            <div class="col-lg-10 col-lg-offset-1 list-group-item addWidthOnMenuOpen">
                <?php
                include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
                ?>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/Gallery/view/footer.php';
        ?>
    </body>
</html>
<?php include $global['systemRootPath'] . 'objects/include_end.php'; ?>
