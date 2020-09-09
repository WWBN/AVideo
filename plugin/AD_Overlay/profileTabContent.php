<?php
$obj = AVideoPlugin::getObjectDataIfEnabled('AD_Overlay');

$ad = new AD_Overlay_Code(0);
$ad->loadFromUser(User::getId());
?>
<div id="adOverlay" class="tab-pane fade"  style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-heading">
            AD Code
        </div>
        <div class="panel-body">
            <textarea class="form-control" rows="10" id="addOverlayCode"><?php echo $ad->getCode(); ?></textarea>
            <button class="btn btn-success btn-block" type="button" onclick="saveCode()"><?php echo __("Save Ad Code") ?></button>
        </div>
    </div>
</div>
<script>
    function saveCode() {
        $.ajax({
            url: '<?php echo $global['webSiteRootURL']; ?>plugin/AD_Overlay/saveCode.json.php',
            data: {
                "addOverlayCode": $('#addOverlayCode').val()
            },
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                } else {
                    avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your code has been saved!"); ?>", "success");
                }
                modal.hidePleaseWait();
            }
        });
    }
    $(document).ready(function () {

    });
</script>