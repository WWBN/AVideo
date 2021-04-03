<?php
$obj = AVideoPlugin::getObjectData("LoginControl");

$pass = time();
$keys = createKeys('Test <test@example.com>', $pass);
?>
<link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
<div id="loginHistory" class="tab-pane fade"  style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo __("Login History"); ?>
        </div>
        <div class="panel-body">
            <table id="logincontrol_historyTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php echo __("When"); ?></th>
                        <th><?php echo __("IP"); ?></th>
                        <th><?php echo __("Device"); ?></th>
                        <th><?php echo __("Type"); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th><?php echo __("When"); ?></th>
                        <th><?php echo __("IP"); ?></th>
                        <th><?php echo __("Device"); ?></th>
                        <th><?php echo __("Type"); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php 
if($obj->enablePGP2FA){
?>
<div id="pgp2fa" class="tab-pane fade"  style="padding: 10px 0;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <?php echo __("PGP Public Key"); ?>
            <button class="btn btn-default btn-xs pull-right" onclick="avideoModalIframe(webSiteRootURL + 'plugin/LoginControl/pgp/keys.php')">
                <i class="fas fa-key"></i> <?php echo __('Generate Keys'); ?>/<?php echo __('Tools'); ?>
            </button>
        </div>
        <div class="panel-body">
            <div class="alert alert-info">
                <?php echo __('If the system finds a valid public key we will challenge you to decrypt a message so that you can log into the system. so make sure you have the private key equivalent to this public key'); ?>
            </div>
            <textarea class="form-control" rows="10" id="publicKey" placeholder="<?php echo $keys['public']; ?>"><?php echo LoginControl::getPGPKey(User::getId()); ?></textarea>
        </div>
        <div class="panel-footer">
            <button class="btn btn-block btn-primary" onclick="savePGP();"><?php echo __('Save PGP Key') ?></button>
        </div>
    </div>
</div>
<?php
}
?>
<script type="text/javascript" src="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.js"></script>
<script>
                $(document).ready(function () {
                    var logincontrol_historytableVar = $('#logincontrol_historyTable').DataTable({
                        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/LoginControl/listLastLogins.json.php",
                        "columns": [
                            {"data": "time_ago"},
                            {"data": "ip"},
                            {"data": "device"},
                            {"data": "type"},
                        ],
                        "order": [],
                        select: true,
                    });
                });

                function savePGP() {
                    modal.showPleaseWait();
                    $.ajax({
                        url: webSiteRootURL + 'plugin/LoginControl/pgp/savePublicKey.json.php',
                        method: 'POST',
                        data: {
                            'publicKey': $('#publicKey').val()
                        },
                        success: function (response) {
                            if (response.error) {
                                avideoAlertError(response.msg);
                            } else {
                                avideoToastSuccess("<?php echo __('Saved'); ?>");
                            }
                            modal.hidePleaseWait();
                        }
                    });
                }
</script>