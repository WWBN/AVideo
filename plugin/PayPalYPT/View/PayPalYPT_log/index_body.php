<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
?>

<div class="row">
    <div class="col-sm-4">
        <div class="panel panel-default ">
            <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
            <div class="panel-body">
                <form id="panelPayPalYPT_logForm">
                    <div class="row">
                        <input type="hidden" name="id" id="PayPalYPT_logid" value="" >
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logagreement_id">
                                <?php echo __("Agreement Id"); ?>:
                            </label>
                            <input type="text" id="PayPalYPT_logagreement_id" name="agreement_id" class="form-control input-sm" 
                                   placeholder="<?php echo __("Agreement Id"); ?>">
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logusers_id"><?php echo __("Users Id"); ?>:</label>
                            <select class="form-control input-sm" name="users_id" id="PayPalYPT_logusers_id">
                                <?php
                                $options = User::getAllUsers(false, array(), "a");
                                foreach ($options as $value) {
                                    echo '<option value="' . $value['id'] . '">' . $value['identification'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logjson"><?php echo __("Json"); ?>:</label>
                            <textarea id="PayPalYPT_logjson" name="json" class="form-control input-sm" placeholder="<?php echo __("Json"); ?>" required="true"></textarea>
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logrecurring_payment_id"><?php echo __("Recurring Payment Id"); ?>:</label>
                            <input type="text" id="PayPalYPT_logrecurring_payment_id" name="recurring_payment_id" class="form-control input-sm" 
                                   placeholder="<?php echo __("Recurring Payment Id"); ?>">
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logvalue"><?php echo __("Value"); ?>:</label>
                            <input type="number" step="0.01" id="PayPalYPT_logvalue" name="value" class="form-control input-sm" placeholder="<?php echo __("Value"); ?>" required="true">
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="PayPalYPT_logtoken"><?php echo __("Token"); ?>:</label>
                            <input type="text" id="PayPalYPT_logtoken" name="token" class="form-control input-sm" placeholder="<?php echo __("Token"); ?>" required="true">
                        </div>
                        <div class="form-group col-sm-12">
                            <div class="btn-group pull-right">
                                <span class="btn btn-success" id="newPayPalYPT_logLink" onclick="clearPayPalYPT_logForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="panel panel-default ">
            <div class="panel-heading"><i class="fas fa-edit"></i> <?php echo __("Edit"); ?></div>
            <div class="panel-body">
                <table id="PayPalYPT_logTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __("Json"); ?></th>
                            <th><?php echo __("Value"); ?></th>
                            <th><?php echo __("Token"); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th><?php echo __("Json"); ?></th>
                            <th><?php echo __("Value"); ?></th>
                            <th><?php echo __("Token"); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<div id="PayPalYPT_logbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_PayPalYPT_log btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_PayPalYPT_log btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearPayPalYPT_logForm() {
        $('#PayPalYPT_logid').val('');
        $('#PayPalYPT_logagreement_id').val('');
        $('#PayPalYPT_logusers_id').val('');
        $('#PayPalYPT_logjson').val('');
        $('#PayPalYPT_logrecurring_payment_id').val('');
        $('#PayPalYPT_logvalue').val('');
        $('#PayPalYPT_logtoken').val('');
    }
    $(document).ready(function () {
        $('#addPayPalYPT_logBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/PayPalYPT/View/addPayPalYPT_logVideo.php',
                data: $('#panelPayPalYPT_logForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelPayPalYPT_logForm").trigger("reset");
                    }
                    clearPayPalYPT_logForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var PayPalYPT_logtableVar = $('#PayPalYPT_logTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/PayPalYPT/View/PayPalYPT_log/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "json"},
                {"data": "value"},
                {"data": "token"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#PayPalYPT_logbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newPayPalYPT_log').on('click', function (e) {
            e.preventDefault();
            $('#panelPayPalYPT_logForm').trigger("reset");
            $('#PayPalYPT_logid').val('');
        });
        $('#panelPayPalYPT_logForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/PayPalYPT/View/PayPalYPT_log/add.json.php',
                data: $('#panelPayPalYPT_logForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelPayPalYPT_logForm").trigger("reset");
                    }
                    PayPalYPT_logtableVar.ajax.reload();
                    $('#PayPalYPT_logid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#PayPalYPT_logTable').on('click', 'button.delete_PayPalYPT_log', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = PayPalYPT_logtableVar.row(tr).data();
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                    .then(function (willDelete) {
                        if (willDelete) {
                            modal.showPleaseWait();
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/PayPalYPT/View/PayPalYPT_log/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                PayPalYPT_logtableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#PayPalYPT_logTable').on('click', 'button.edit_PayPalYPT_log', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = PayPalYPT_logtableVar.row(tr).data();
            $('#PayPalYPT_logid').val(data.id);
            $('#PayPalYPT_logagreement_id').val(data.agreement_id);
            $('#PayPalYPT_logusers_id').val(data.users_id);
            $('#PayPalYPT_logjson').val(data.json);
            $('#PayPalYPT_logrecurring_payment_id').val(data.recurring_payment_id);
            $('#PayPalYPT_logvalue').val(data.value);
            $('#PayPalYPT_logtoken').val(data.token);
        });
    });
</script>
