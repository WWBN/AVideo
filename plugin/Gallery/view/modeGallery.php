<?php
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
$leaderBoardTop = getAdsLeaderBoardTop();
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
            <?php
            if(!empty($leaderBoardTop)){
                echo '<div class="row text-center" style="padding: 10px;">'.$leaderBoardTop.'</div>';
            }else{
                echo '<!-- getAdsLeaderBoardTop is empty -->';
            }
            ?>
            <div class="col-lg-10 col-lg-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body" style="overflow: hidden;">
                        <?php
                    include $global['systemRootPath'] . 'view/include/categoryTop.php';
                    include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
                    ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/Gallery/view/footer.php';
        ?>
    </body>
</html>
<?php include_once $global['systemRootPath'] . 'objects/include_end.php'; ?>
