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
                        <form id="panelMeet_schedule_has_users_groupsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Meet_schedule_has_users_groupsid" value="" >
<div class="form-group col-sm-12">
                                    <label for="Meet_schedule_has_users_groupsmeet_schedule_id"><?php echo __("Meet Schedule Id"); ?>:</label>
                                    <select class="form-control input-sm" name="meet_schedule_id" id="Meet_schedule_has_users_groupsmeet_schedule_id">
                                        <?php
                                        $options = Meet_schedule_has_users_groups::getAllMeet_schedule();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
<div class="form-group col-sm-12">
                                    <label for="Meet_schedule_has_users_groupsusers_groups_id"><?php echo __("Users Groups Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_groups_id" id="Meet_schedule_has_users_groupsusers_groups_id">
                                        <?php
                                        $options = Meet_schedule_has_users_groups::getAllUsers_groups();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newMeet_schedule_has_users_groupsLink" onclick="clearMeet_schedule_has_users_groupsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Meet_schedule_has_users_groupsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
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
<div id="Meet_schedule_has_users_groupsbtnModelLinks" style="display:none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Meet_schedule_has_users_groups btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Meet_schedule_has_users_groups btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearMeet_schedule_has_users_groupsForm() {
    $('#Meet_schedule_has_users_groupsid').val('');
$('#Meet_schedule_has_users_groupsmeet_schedule_id').val('');
$('#Meet_schedule_has_users_groupsusers_groups_id').val('');
    }
    $(document).ready(function () {
    $('#addMeet_schedule_has_users_groupsBtn').click(function () {
    $.ajax({
    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/addMeet_schedule_has_users_groupsVideo.php',
            data: $('#panelMeet_schedule_has_users_groupsForm').serialize(),
            type: 'post',
            success: function (response) {
            if (response.error) {
            avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
            } else {
            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
            $("#panelMeet_schedule_has_users_groupsForm").trigger("reset");
            }
            clearMeet_schedule_has_users_groupsForm();
            tableVideos.ajax.reload();
            modal.hidePleaseWait();
            }
    });
    });
    var Meet_schedule_has_users_groupstableVar = $('#Meet_schedule_has_users_groupsTable').DataTable({
    "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule_has_users_groups/list.json.php",
            "columns": [
            {"data": "id"},
            {
            sortable: false,
                    data: null,
                    defaultContent: $('#Meet_schedule_has_users_groupsbtnModelLinks').html()
            }
            ],
            select: true,
    });
    $('#newMeet_schedule_has_users_groups').on('click', function (e) {
    e.preventDefault();
    $('#panelMeet_schedule_has_users_groupsForm').trigger("reset");
    $('#Meet_schedule_has_users_groupsid').val('');
    });
    $('#panelMeet_schedule_has_users_groupsForm').on('submit', function (e) {
    e.preventDefault();
    modal.showPleaseWait();
    $.ajax({
    url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule_has_users_groups/add.json.php',
            data: $('#panelMeet_schedule_has_users_groupsForm').serialize(),
            type: 'post',
            success: function (response) {
            if (response.error) {
            avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
            } else {
            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
            $("#panelMeet_schedule_has_users_groupsForm").trigger("reset");
            }
            Meet_schedule_has_users_groupstableVar.ajax.reload();
            $('#Meet_schedule_has_users_groupsid').val('');
            modal.hidePleaseWait();
            }
    });
    });
    $('#Meet_schedule_has_users_groupsTable').on('click', 'button.delete_Meet_schedule_has_users_groups', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Meet_schedule_has_users_groupstableVar.row(tr).data();
    swal({
    title: "<?php echo __("Are you sure?"); ?>",
            text: "<?php echo __("You will not be able to recover this action!"); ?>",
            icon: "warning",
            buttons: true,
            dangerMode: true,
    })
            .then(function(willDelete) {
            if (willDelete) {
            modal.showPleaseWait();
            $.ajax({
            type: "POST",
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule_has_users_groups/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
            avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
            }
            Meet_schedule_has_users_groupstableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Meet_schedule_has_users_groupsTable').on('click', 'button.edit_Meet_schedule_has_users_groups', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Meet_schedule_has_users_groupstableVar.row(tr).data();
    $('#Meet_schedule_has_users_groupsid').val(data.id);
$('#Meet_schedule_has_users_groupsmeet_schedule_id').val(data.meet_schedule_id);
$('#Meet_schedule_has_users_groupsusers_groups_id').val(data.users_groups_id);
    });
    });
</script>
