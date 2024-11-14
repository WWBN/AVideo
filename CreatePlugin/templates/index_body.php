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
                        <form id="panel{classname}Form">
                            <div class="row">
                                {columnsForm}
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="new{classname}Link" onclick="clear{classname}Form()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="{classname}Table" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    {columnsGrid}
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    {columnsGrid}
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
<div id="{classname}btnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_{classname} btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_{classname} btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
</div>
<script type="text/javascript">
    function clear{classname}Form() {
    {$columnsClearJQuery}
    }
    $(document).ready(function () {
    $('#add{classname}Btn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/{pluginName}/View/add{classname}Video.php',
            data: $('#panel{classname}Form').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panel{classname}Form").trigger("reset");
                }
                clear{classname}Form();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var {classname}tableVar = $('#{classname}Table').DataTable({
        serverSide: true,
        "ajax": webSiteRootURL+"plugin/{pluginName}/View/{classname}/list.json.php",
        "columns": [
        {columnsDatatable},
        {
        sortable: false,
                data: null,
                defaultContent: $('#{classname}btnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#new{classname}').on('click', function (e) {
    e.preventDefault();
    $('#panel{classname}Form').trigger("reset");
    $('#{classname}id').val('');
    });
    $('#panel{classname}Form').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/{pluginName}/View/{classname}/add.json.php',
            data: $('#panel{classname}Form').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast(__("Your register has been saved!"));
                    $("#panel{classname}Form").trigger("reset");
                }
                {classname}tableVar.ajax.reload();
                $('#{classname}id').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#{classname}Table').on('click', 'button.delete_{classname}', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = {classname}tableVar.row(tr).data();
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
                    url: webSiteRootURL+"plugin/{pluginName}/View/{classname}/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            {classname}tableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#{classname}Table').on('click', 'button.edit_{classname}', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = {classname}tableVar.row(tr).data();
    {$columnsEdit}
    });
    });
</script>
{columnsFooter}