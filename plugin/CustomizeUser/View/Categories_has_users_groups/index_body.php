<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("Must be admin");
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
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                    <div class="panel-body">
                        <form id="panelCategories_has_users_groupsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Categories_has_users_groupsid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Categories_has_users_groupscategories_id"><i class="fas fa-list"></i> <?php echo __("Categories"); ?>:</label>
                                    <?php
                                    echo Layout::getCategorySelect("categories_id", "", "Categories_has_users_groupscategories_id", "input-sm");
                                    ?>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Categories_has_users_groupsusers_groups_id"><i class="fas fa-users"></i> <?php echo __("Users Groups"); ?>:</label>
                                    <?php
                                    echo Layout::getUserGroupsSelect("users_groups_id", "", "Categories_has_users_groupsusers_groups_id", "input-sm");
                                    ?>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Categories_has_users_groupsstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newCategories_has_users_groupsLink" onclick="clearCategories_has_users_groupsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Categories_has_users_groupsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Category"); ?></th>
                                    <th><?php echo __("User Group"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Category"); ?></th>
                                    <th><?php echo __("User Group"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
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
<div id="Categories_has_users_groupsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Categories_has_users_groups btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Categories_has_users_groups btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearCategories_has_users_groupsForm() {
        $('#Categories_has_users_groupsid').val('');
        $('#Categories_has_users_groupscategories_id').val('');
        $('#Categories_has_users_groupscategories_id').trigger('change');
        $('#Categories_has_users_groupsusers_groups_id').val('');
        $('#Categories_has_users_groupsusers_groups_id').trigger('change');
        $('#Categories_has_users_groupsstatus').val('');
    }
    $(document).ready(function () {
        $('#addCategories_has_users_groupsBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/addCategories_has_users_groupsVideo.php',
                data: $('#panelCategories_has_users_groupsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelCategories_has_users_groupsForm").trigger("reset");
                    }
                    clearCategories_has_users_groupsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Categories_has_users_groupstableVar = $('#Categories_has_users_groupsTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Categories_has_users_groups/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "name"},
                {"data": "group_name"},
                {"data": "status"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Categories_has_users_groupsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newCategories_has_users_groups').on('click', function (e) {
            e.preventDefault();
            $('#panelCategories_has_users_groupsForm').trigger("reset");
            $('#Categories_has_users_groupsid').val('');
        });
        $('#panelCategories_has_users_groupsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Categories_has_users_groups/add.json.php',
                data: $('#panelCategories_has_users_groupsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelCategories_has_users_groupsForm").trigger("reset");
                    }
                    Categories_has_users_groupstableVar.ajax.reload();
                    $('#Categories_has_users_groupsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Categories_has_users_groupsTable').on('click', 'button.delete_Categories_has_users_groups', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Categories_has_users_groupstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Categories_has_users_groups/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Categories_has_users_groupstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Categories_has_users_groupsTable').on('click', 'button.edit_Categories_has_users_groups', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Categories_has_users_groupstableVar.row(tr).data();
            $('#Categories_has_users_groupsid').val(data.id);
            $('#Categories_has_users_groupscategories_id').val(data.categories_id);
            $('#Categories_has_users_groupscategories_id').trigger('change');
            $('#Categories_has_users_groupsusers_groups_id').val(data.users_groups_id);
            $('#Categories_has_users_groupsusers_groups_id').trigger('change');
            $('#Categories_has_users_groupsstatus').val(data.status);
        });
    });
</script>
