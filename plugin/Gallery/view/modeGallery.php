<?php
$isFirstPage = 1;
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
if (empty($_REQUEST['catName'])) {
    $leaderBoardTop = getAdsLeaderBoardTop();
}
echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
$_page = new Page(array($siteTitle), 'mainPage');
?>
<div class="<?php echo Gallery::getContaierClass('avideoLoadPage'); ?>">
    <?php
    if (!empty($leaderBoardTop)) {
        echo '<!-- leaderBoardTop start --><div class="row text-center" style="padding: 10px;">' . $leaderBoardTop . '</div><!-- leaderBoardTop end -->';
    } else {
        echo '<!-- getAdsLeaderBoardTop is empty -->';
    }
    echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
    ?>
    <div class="panel panel-default">
        <div class="panel-body" style="overflow: hidden;">
            <?php
        echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
            include_once $global['systemRootPath'] . 'view/include/categoryTop.php';
            echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
            include $global['systemRootPath'] . 'plugin/Gallery/view/mainArea.php';
            echo '<!-- page='. (@$_GET['page']) .' line='.__LINE__.' file='.basename(__FILE__).' -->';
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>