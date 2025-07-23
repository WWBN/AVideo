<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage('Admins only');
}
?>
<div class="container-fluid">
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fas fa-cog"></i> <?php echo __("Configurations"); ?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                    <div class="panel-body">
                        <form id="panelAnet_webhook_logForm">
                            <div class="row">
                                
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newAnet_webhook_logLink" onclick="clearAnet_webhook_logForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Anet_webhook_logTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="Anet_webhook_logbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Anet_webhook_log btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Anet_webhook_log btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
</div>
<script type="text/javascript">
    function clearAnet_webhook_logForm() {
    
    }
    $(document).ready(function () {
    $('#addAnet_webhook_logBtn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/AuthorizeNet/View/addAnet_webhook_logVideo.php',
            data: $('#panelAnet_webhook_logForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAnet_webhook_logForm").trigger("reset");
                }
                clearAnet_webhook_logForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Anet_webhook_logtableVar = $('#Anet_webhook_logTable').DataTable({
        serverSide: true,
        "ajax": webSiteRootURL+"plugin/AuthorizeNet/View/Anet_webhook_log/list.json.php",
        "columns": [
        ,
        {
        sortable: false,
                data: null,
                defaultContent: $('#Anet_webhook_logbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newAnet_webhook_log').on('click', function (e) {
    e.preventDefault();
    $('#panelAnet_webhook_logForm').trigger("reset");
    $('#Anet_webhook_logid').val('');
    });
    $('#panelAnet_webhook_logForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/AuthorizeNet/View/Anet_webhook_log/add.json.php',
            data: $('#panelAnet_webhook_logForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast(__("Your register has been saved!"));
                    $("#panelAnet_webhook_logForm").trigger("reset");
                }
                Anet_webhook_logtableVar.ajax.reload();
                $('#Anet_webhook_logid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Anet_webhook_logTable').on('click', 'button.delete_Anet_webhook_log', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Anet_webhook_logtableVar.row(tr).data();
    swal({
    title: __("Are you sure?"),
            text: __("You will not be able to recover this action!"),
            icon: "warning",
            buttons: true,
            dangerMode: true,
    })
            .then(function (willDelete) {
            if (willDelete) {
            modal.showPleaseWait();
            $.ajax({
            type: "POST",
                    url: webSiteRootURL+"plugin/AuthorizeNet/View/Anet_webhook_log/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Anet_webhook_logtableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Anet_webhook_logTable').on('click', 'button.edit_Anet_webhook_log', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Anet_webhook_logtableVar.row(tr).data();
    
    });
    });
</script>
