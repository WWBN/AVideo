<?php
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
$leaderBoardTop = getAdsLeaderBoardTop();
_ob_start();
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php
            echo $siteTitle;
            ?></title>
        <?php include $global['systemRootPath'] . 'view/include/head.php'; ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php include $global['systemRootPath'] . 'view/include/navbar.php'; ?>
        <div class="<?php echo Gallery::getContaierClass('avideoLoadPage'); ?>">
            <?php
            if(!empty($leaderBoardTop)){
                echo '<!-- leaderBoardTop start --><div class="row text-center" style="padding: 10px;">'.$leaderBoardTop.'</div><!-- leaderBoardTop end -->';
            }else{
                echo '<!-- getAdsLeaderBoardTop is empty -->';
            }
            ?>
            <div class="panel panel-default">
                <div class="panel-body" style="overflow: hidden;">
                    <?php
                include $global['systemRootPath'] . 'view/include/categoryTop.php';
                include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
                ?>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'plugin/Gallery/view/footer.php';
        ?>
    </body>
</html>
<?php include_once $global['systemRootPath'] . 'objects/include_end.php'; ?>
