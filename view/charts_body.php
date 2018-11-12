
<div class="container-fluid">
    <div class="list-group-item clear clearfix">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#dashboard"><i class="fas fa-tachometer-alt"></i> <?php echo __("Dashboard"); ?></a></li>
            <li><a data-toggle="tab" id="viewperchannel" href="#menu1"><i class="fab fa-youtube"></i> <i class="fa fa-eye"></i> <?php echo __("Video views - per Channel"); ?></a></li>
            <li><a data-toggle="tab" id="commentthumbs" href="#menu2"><i class="fa fa-comments"></i> <i class="fa fa-thumbs-up"></i> <?php echo __("Comment thumbs up - per Person"); ?></a></li>
            <li><a data-toggle="tab" id="videothumbs" href="#menu3"><i class="fab fa-youtube"></i> <i class="fa fa-thumbs-up"></i> <?php echo __("Video thumbs up - per Channel"); ?></a></li>
            <?php echo YouPHPTubePlugin::getChartTabs(); ?>
        </ul>

        <div class="tab-content">
            <div id="dashboard" class="tab-pane fade in active" style="padding: 10px;">
                <?php
                include $global['systemRootPath'] . 'view/report0.php';
                ?>
            </div>
            <div id="menu1" class="tab-pane fade" style="padding: 10px;">
                <?php
                include $global['systemRootPath'] . 'view/report1.php';
                ?>
            </div>
            <div id="menu2" class="tab-pane fade" style="padding: 10px;">
                <?php
                include $global['systemRootPath'] . 'view/report2.php';
                ?>
            </div>
            <div id="menu3" class="tab-pane fade" style="padding: 10px;">
                <?php
                include $global['systemRootPath'] . 'view/report3.php';
                ?>
            </div>
            <?php echo YouPHPTubePlugin::getChartContent(); ?>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.js" type="text/javascript"></script>
<?php
include_once $global['systemRootPath'] . 'view/include/footer.php';
?>
<script type="text/javascript">
    $(document).ready(function () {
<?php if (!empty($_GET['jump'])) { ?>
            $('#<?php echo $_GET['jump']; ?>').click();
<?php } ?>
    });
</script>