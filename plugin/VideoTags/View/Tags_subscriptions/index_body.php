<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("You can not do this");
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
                        <form id="panelTags_subscriptionsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Tags_subscriptionsid" value="" >
<div class="form-group col-sm-12">
                                    <label for="Tags_subscriptionstags_id"><?php echo __("Tags Id"); ?>:</label>
                                    <select class="form-control input-sm" name="tags_id" id="Tags_subscriptionstags_id">
                                        <?php
                                        $options = Tags_subscriptions::getAllTags();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
<div class="form-group col-sm-12">
                                    <label for="Tags_subscriptionsusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Tags_subscriptionsusers_id">
                                        <?php
                                        $options = Tags_subscriptions::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newTags_subscriptionsLink" onclick="clearTags_subscriptionsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Tags_subscriptionsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
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
<div id="Tags_subscriptionsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Tags_subscriptions btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Tags_subscriptions btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearTags_subscriptionsForm() {
    $('#Tags_subscriptionsid').val('');
$('#Tags_subscriptionstags_id').val('');
$('#Tags_subscriptionsusers_id').val('');
    }
    $(document).ready(function () {
    $('#addTags_subscriptionsBtn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/VideoTags/View/addTags_subscriptionsVideo.php',
            data: $('#panelTags_subscriptionsForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelTags_subscriptionsForm").trigger("reset");
                }
                clearTags_subscriptionsForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Tags_subscriptionstableVar = $('#Tags_subscriptionsTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/VideoTags/View/Tags_subscriptions/list.json.php",
        "columns": [
        {"data": "id"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#Tags_subscriptionsbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newTags_subscriptions').on('click', function (e) {
    e.preventDefault();
    $('#panelTags_subscriptionsForm').trigger("reset");
    $('#Tags_subscriptionsid').val('');
    });
    $('#panelTags_subscriptionsForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/VideoTags/View/Tags_subscriptions/add.json.php',
            data: $('#panelTags_subscriptionsForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelTags_subscriptionsForm").trigger("reset");
                }
                Tags_subscriptionstableVar.ajax.reload();
                $('#Tags_subscriptionsid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Tags_subscriptionsTable').on('click', 'button.delete_Tags_subscriptions', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Tags_subscriptionstableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/VideoTags/View/Tags_subscriptions/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Tags_subscriptionstableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Tags_subscriptionsTable').on('click', 'button.edit_Tags_subscriptions', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Tags_subscriptionstableVar.row(tr).data();
    $('#Tags_subscriptionsid').val(data.id);
$('#Tags_subscriptionstags_id').val(data.tags_id);
$('#Tags_subscriptionsusers_id').val(data.users_id);
    });
    });
</script>
