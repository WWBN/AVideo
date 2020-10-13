<?php
$obj = AVideoPlugin::getObjectDataIfEnabled('AD_Overlay');

$ad = new AD_Overlay_Code(0);
$ad->loadFromUser(User::getId());
?>
<link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
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
<script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
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
</script>