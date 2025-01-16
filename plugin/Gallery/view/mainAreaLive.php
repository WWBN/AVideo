<?php
require_once dirname(__FILE__) . '/../../../videos/configuration.php';
$isFirstPage = 1;
include $global['systemRootPath'] . 'plugin/Gallery/view/topLogic.php';
$leaderBoardTop = getAdsLeaderBoardTop();
$objLive = AVideoPlugin::getDataObject('Live');
$_page = new Page(array('Live'));
?>
<div class="<?php echo Gallery::getContaierClass(); ?>">
    <?php
    if (!empty($leaderBoardTop)) {
        echo '<div class="row text-center" style="padding: 10px;">' . $leaderBoardTop . '</div>';
    } else {
        echo '<!-- getAdsLeaderBoardTop is empty -->';
    }
    ?>

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row mainArea">
                <?php
                    include __DIR__.'/liveHTMLRows.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>
