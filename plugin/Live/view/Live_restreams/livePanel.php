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
    </div>
    <div class="panel-body">
        <?php
        include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/livePanelForm.php';
        ?>
    </div>
    <div class="panel-footer clearfix">
        <?php
        include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/getLiveKey.php';
        ?>
        <div class="btn-group pull-right">
            <button type="button" class="btn btn-primary" onclick="testRestreamer();">
                <i class="fas fa-check"></i>
                <?php echo __('Test'); ?>
            </button>
            <button type="button" class="btn btn-success" onclick="avideoModalIframe(webSiteRootURL+'plugin/Live/view/Live_restreams/activeLives.php');">
                <i class="fas fa-wifi"></i>
                <?php echo __('Active Lives'); ?>
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
