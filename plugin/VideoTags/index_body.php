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

<div class="container">

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading"><?php echo __("Create Tag Type"); ?></div>
                <div class="panel-body">
                    <form id="panelForm">
                        <div class="row">
                            <input type="hidden" name="campId" id="campId" value="" >
                            <div class="form-group col-sm-12">
                                <label for="name"><?php echo __("Name"); ?>:</label>
                                <input type="text" id="name" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                            </div>
                            <div class="form-group col-sm-12">
                                <div class="btn-group pull-right">
                                    <span class="btn btn-success" id="newLiveLink"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default ">
                <div class="panel-heading"><?php echo __("Tag Types"); ?></div>
                <div class="panel-body">
                    <table id="campaignTable" class="display" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th><?php echo __("Name"); ?></th>
                                <th><?php echo __("Created"); ?></th>
                                <th><?php echo __("Modified"); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th><?php echo __("Name"); ?></th>
                                <th><?php echo __("Created"); ?></th>
                                <th><?php echo __("Modified"); ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="btnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="editor_edit_link btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="editor_delete_link btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
<script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        var tableLinks = $('#campaignTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/VideoTags/tagTypes.json.php",
            "columns": [
                {"data": "name"},
                {"data": "created"},
                {"data": "modified"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#btnModelLinks').html(), "width": "60px"
                }
            ],
            select: true,
        });

        $('#campaignTable').on('click', 'button.editor_delete_link', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                closeOnConfirm: true
            },
                    function () {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/VideoTags/deleteTagTypes.json.php",
                            data: data

                        }).done(function (resposta) {
                            if (resposta.error) {
                                swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                            }
                            tableLinks.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    });
        });

        $('#campaignTable').on('click', 'button.editor_edit_link', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();
            $('#campId').val(data.id);
            $('#name').val(data.name);
            $('#startDate').val(data.start_date);
            $('#endDate').val(data.end_date);
            $('#maxPrints').val(data.cpm_max_prints);
            $('#status').val(data.status);
            //$('#visibility').val(data.visibility);
        });

        $('#panelForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/VideoTags/addTagTypes.php',
                data: $('#panelForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");

                        $("#panelForm").trigger("reset");
                    }
                    tableLinks.ajax.reload();
                    $('#campId').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        
        $('#newLiveLink').on('click', function (e) {
            e.preventDefault();
            $('#panelForm').trigger("reset");
            $('#campId').val('');
        });
    });
</script>