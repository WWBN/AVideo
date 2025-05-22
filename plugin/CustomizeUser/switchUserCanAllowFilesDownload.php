<?php
$uid = _uniqid();
?>
<div class="material-switch">
    <input class="playerSwitchDefault" data-toggle="toggle" type="checkbox" value="" id="switch<?php echo $uid; ?>" <?php echo (CustomizeUser::canDownloadVideosFromUser($users_id)) ? "checked" : ""; ?>>
    <label for="switch<?php echo $uid; ?>" class="label-primary"></label>
</div>
<script>
    $(document).ready(function () {
        $('#switch<?php echo $uid; ?>').change(function (e) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/CustomizeUser/set.json.php',
                data: {"type": "userCanAllowFilesDownload", "value": $('#switch<?php echo $uid; ?>').is(":checked")},
                type: 'post',
                success: function (response) {
                    modal.hidePleaseWait();
                }
            });
        });
    });
</script>