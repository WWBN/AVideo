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
                        <form id="panelBtc_invoicesForm">
                            <div class="row">
                                
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newBtc_invoicesLink" onclick="clearBtc_invoicesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Btc_invoicesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
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
<div id="Btc_invoicesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Btc_invoices btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Btc_invoices btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
</div>
<script type="text/javascript">
    function clearBtc_invoicesForm() {
    
    }
    $(document).ready(function () {
    $('#addBtc_invoicesBtn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/BTCPayments/View/addBtc_invoicesVideo.php',
            data: $('#panelBtc_invoicesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelBtc_invoicesForm").trigger("reset");
                }
                clearBtc_invoicesForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Btc_invoicestableVar = $('#Btc_invoicesTable').DataTable({
        serverSide: true,
        "ajax": webSiteRootURL+"plugin/BTCPayments/View/Btc_invoices/list.json.php",
        "columns": [
        ,
        {
        sortable: false,
                data: null,
                defaultContent: $('#Btc_invoicesbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newBtc_invoices').on('click', function (e) {
    e.preventDefault();
    $('#panelBtc_invoicesForm').trigger("reset");
    $('#Btc_invoicesid').val('');
    });
    $('#panelBtc_invoicesForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/BTCPayments/View/Btc_invoices/add.json.php',
            data: $('#panelBtc_invoicesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast(__("Your register has been saved!"));
                    $("#panelBtc_invoicesForm").trigger("reset");
                }
                Btc_invoicestableVar.ajax.reload();
                $('#Btc_invoicesid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Btc_invoicesTable').on('click', 'button.delete_Btc_invoices', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Btc_invoicestableVar.row(tr).data();
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
                    url: webSiteRootURL+"plugin/BTCPayments/View/Btc_invoices/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Btc_invoicestableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Btc_invoicesTable').on('click', 'button.edit_Btc_invoices', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Btc_invoicestableVar.row(tr).data();
    
    });
    });
</script>
