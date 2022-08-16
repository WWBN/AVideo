<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!Live::canRestream()) {
    return false;
}
?>
<div class="panel panel-default">
    <div class="panel-heading tabbable-line">
        <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#restreamsForm"> <i class="fas fa-sync"></i> <?php echo __('Restreams'); ?></a></li>
            
            <?php
            if (isOnDeveloperMode()) {
            ?>
            <li><a data-toggle="tab" href="#restreamsActive"><i class="fas fa-wifi"></i> <?php echo __('Active Lives'); ?></a></li>
            <?php
            }
            ?>
        </ul>
    </div>
    <div class="panel-body">
        
        <div class="tab-content">
            <div id="restreamsForm" class="tab-pane fade in active">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanelForm.php';
                ?>
            </div>
            <?php
            if (isOnDeveloperMode()) {
            ?>
            <div id="restreamsActive" class="tab-pane fade">
                <?php
                include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanelActiveLives.php';
                ?>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <?php
        if (isOnDeveloperMode()) {
            include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/getLiveKey.php';
        }
        ?>
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-info " onclick="avideoModalIframeFull(webSiteRootURL + 'plugin/Live/view/Live_restreams_logs/');">
                <i class="fas fa-clipboard-list"></i>
                <?php echo __('Logs'); ?>
            </button>
            <button type="button" class="btn btn-primary" onclick="testRestreamer();">
                <i class="fas fa-check"></i>
                <?php echo __('Test'); ?>
            </button>
        </div>
    </div>
</div>
<script type="text/javascript">
    function testRestreamer() {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/view/Live_restreams/testRestreamer.json.php',
            success: function (response) {
                avideoResponse(response);
                modal.hidePleaseWait();
            }
        });
    }
</script>
