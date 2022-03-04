<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isACompany()) {
    forbiddenPage("You must be a company to see this");
}
?>


<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fas fa-cog"></i> <?php echo __("Configurations"); ?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Add a new affiliate"); ?></div>
                    <div class="panel-body">
                        <form id="panelUsers_affiliationsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Users_affiliationsid" value="" >
                                <div class="form-group col-sm-12">
                                    <?php
                                        $updateUserAutocomplete = Layout::getUserAutocomplete(0, 'users_id_affiliate', array('isCompany'=>0));
                                    ?>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newUsers_affiliationsLink" onclick="clearUsers_affiliationsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Users_affiliationsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Affiliate"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Company Agree Date"); ?></th>
                                    <th><?php echo __("Affiliate Agree Date"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Affiliate"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Company Agree Date"); ?></th>
                                    <th><?php echo __("Affiliate Agree Date"); ?></th>
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
<div id="Users_affiliationsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Users_affiliations btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Users_affiliations btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearUsers_affiliationsForm() {
        $('#Users_affiliationsid').val('');
        $('#Users_affiliationsusers_id_company').val('');
        $('#users_id_affiliate').val('');
        $('#Users_affiliationsstatus').val('');
        $('#Users_affiliationscompany_agree_date').val('');
        $('#Users_affiliationsaffiliate_agree_date').val('');
        <?php echo $updateUserAutocomplete; ?>
    }
    $(document).ready(function () {
        $('#addUsers_affiliationsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/addUsers_affiliationsVideo.php',
                data: $('#panelUsers_affiliationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_affiliationsForm").trigger("reset");
                    }
                    clearUsers_affiliationsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Users_affiliationstableVar = $('#Users_affiliationsTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "affiliate"},
                {"data": "status"},
                {"data": "company_agree_date"},
                {"data": "affiliate_agree_date"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Users_affiliationsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUsers_affiliations').on('click', function (e) {
            e.preventDefault();
            $('#panelUsers_affiliationsForm').trigger("reset");
            $('#Users_affiliationsid').val('');
        });
        $('#panelUsers_affiliationsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/add.json.php',
                data: $('#panelUsers_affiliationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_affiliationsForm").trigger("reset");
                    }
                    Users_affiliationstableVar.ajax.reload();
                    $('#Users_affiliationsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Users_affiliationsTable').on('click', 'button.delete_Users_affiliations', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_affiliationstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_affiliations/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                Users_affiliationstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Users_affiliationsTable').on('click', 'button.edit_Users_affiliations', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_affiliationstableVar.row(tr).data();
            $('#Users_affiliationsid').val(data.id);
            $('#Users_affiliationsusers_id_company').val(data.users_id_company);
            $('#users_id_affiliate').val(data.users_id_affiliate);
            $('#Users_affiliationsstatus').val(data.status);
            $('#Users_affiliationscompany_agree_date').val(data.company_agree_date);
            $('#Users_affiliationsaffiliate_agree_date').val(data.affiliate_agree_date);
            <?php echo $updateUserAutocomplete; ?>
        });
    });
</script>