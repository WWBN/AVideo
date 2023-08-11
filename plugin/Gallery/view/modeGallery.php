<?php
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
if (empty($_REQUEST['catName'])) {
    $leaderBoardTop = getAdsLeaderBoardTop();
}
$_page = new Page(array($siteTitle), 'mainPage');
?>
<div class="<?php echo Gallery::getContaierClass('avideoLoadPage'); ?>">
    <?php
    if (!empty($leaderBoardTop)) {
        echo '<!-- leaderBoardTop start --><div class="row text-center" style="padding: 10px;">' . $leaderBoardTop . '</div><!-- leaderBoardTop end -->';
    } else {
        echo '<!-- getAdsLeaderBoardTop is empty -->';
    }
    ?>
    <div class="panel panel-default">
        <div class="panel-body" style="overflow: hidden;">
            <?php
            include_once $global['systemRootPath'] . 'view/include/categoryTop.php';
            include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>