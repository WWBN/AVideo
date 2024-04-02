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
            <div class="col-sm-6">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                    <div class="panel-body">
                        <form id="panelUser_notificationsForm">
                            <div class="row">
                                <input type="hidden" name="id" id="User_notificationsid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="User_notifications_title"><?php echo __("Title"); ?>:</label>
                                    <input type="text" id="User_notifications_title" name="title" class="form-control input-sm" placeholder="<?php echo __("Title"); ?>">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="User_notificationsmsg"><?php echo __("Message"); ?>:</label>
                                    <textarea id="User_notificationsmsg" name="msg" class="form-control input-sm" placeholder="<?php echo __("Msg"); ?>" required="true"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <?php
                                    $autocomplete = Layout::getUserAutocomplete(0, 'User_notificationsusers_id');
                                    ?>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationstype"><?php echo __("Type"); ?>:</label>

                                    <select class="form-control input-sm" name="type" id="User_notificationstype">
                                        <?php
                                        foreach (UserNotifications::types as $value) {
                                            echo "<option value=\"{$value}\">{$value}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="User_notificationsstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationspriority"><?php echo __("Priority"); ?>:</label>

                                    <select class="form-control input-sm" name="priority" id="User_notificationspriority">
                                        <?php
                                        for ($index = 1; $index <= 10; $index++) {
                                            echo "<option value=\"{$index}\">{$index}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationsimage"><?php echo __("Image"); ?>:</label>
                                    <input type="text" id="User_notificationsimage" name="image" class="form-control input-sm" placeholder="<?php echo __("Image"); ?>">
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationsicon"><?php echo __("Icon"); ?>:</label>
                                    <?php
                                    echo Layout::getIconsSelect("User_notificationsicon");
                                    ?>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationshref"><?php echo __("Href"); ?>:</label>
                                    <input type="text" id="User_notificationshref" name="href" class="form-control input-sm" placeholder="<?php echo __("Href"); ?>" >
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationsonclick"><?php echo __("Onclick"); ?>:</label>
                                    <input type="text" id="User_notificationsonclick" name="onclick" class="form-control input-sm" placeholder="<?php echo __("Onclick"); ?>" >
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationselement_class"><?php echo __("Element Class"); ?>:</label>
                                    <input type="text" id="User_notificationselement_class" name="element_class" class="form-control input-sm" placeholder="<?php echo __("Class"); ?>" >
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="User_notificationselement_id"><?php echo __("Element ID"); ?>:</label>
                                    <input type="text" name="element_id" id="User_notificationselement_id"  class="form-control input-sm" placeholder="<?php echo __("Element ID"); ?>" >
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newUser_notificationsLink" onclick="clearUser_notificationsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                                        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="fas fa-edit"></i> <?php echo __("Edit"); ?></div>
                    <div class="panel-body">
                        <table id="User_notificationsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Title"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Time Readed"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Title"); ?></th>
                                    <th><?php echo __("Type"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Time Readed"); ?></th>
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
<div id="User_notificationsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_User_notifications btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_User_notifications btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearUser_notificationsForm() {
        $('#User_notificationsid').val('');
        $('#User_notifications_title').val('');
        $('#User_notificationsmsg').val('');
        $('#User_notificationstype').val('');
        $('#User_notificationsstatus').val('');
        //$('#User_notificationstime_readed').val('');
        $('#User_notificationsusers_id').val('');
        $('#User_notificationsimage').val('');
        $('#User_notificationsicon').val('');
        $("select[name='User_notificationsicon']").val('');
        $("select[name='User_notificationsicon']").trigger('change');
        $('#User_notificationshref').val('');
        $('#User_notificationsonclick').val('');
        $('#User_notificationselement_class').val('');
        $('#User_notificationselement_id').val('');
        $('#User_notificationspriority').val('');
    }
    $(document).ready(function () {
        $('#addUser_notificationsBtn').click(function () {
            $.ajax({
                url: webSiteRootURL + 'plugin/UserNotifications/View/addUser_notificationsVideo.php',
                data: $('#panelUser_notificationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUser_notificationsForm").trigger("reset");
                    }
                    clearUser_notificationsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var User_notificationstableVar = $('#User_notificationsTable').DataTable({
            serverSide: true,
            order: [[0, 'desc']],
            "ajax": webSiteRootURL + "plugin/UserNotifications/View/User_notifications/list.json.php",
            "columns": [
                {"data": "id"},
                {
                    sortable: true,
                    data: 'title',
                    "render": function (data, type, full, meta) {
                        var iconHTML = '';
                        if(!empty(full.icon)){
                            iconHTML = '<i class="'+full.icon+'"></i> ';
                        }
                        
                        return iconHTML+full.title;
                    }
                },
                {"data": "type"},
                {"data": "status"},
                {"data": "time_readed"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#User_notificationsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUser_notifications').on('click', function (e) {
            e.preventDefault();
            $('#panelUser_notificationsForm').trigger("reset");
            $('#User_notificationsid').val('');
        });
        $('#panelUser_notificationsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/UserNotifications/View/User_notifications/add.json.php',
                data: $('#panelUser_notificationsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUser_notificationsForm").trigger("reset");
                    }
                    User_notificationstableVar.ajax.reload();
                    $('#User_notificationsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#User_notificationsTable').on('click', 'button.delete_User_notifications', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = User_notificationstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/UserNotifications/View/User_notifications/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlertError(resposta.msg);
                                }
                                User_notificationstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#User_notificationsTable').on('click', 'button.edit_User_notifications', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = User_notificationstableVar.row(tr).data();
            $('#User_notificationsid').val(data.id);
            $('#User_notifications_title').val(data.title);
            $('#User_notificationsmsg').val(data.msg);
            $('#User_notificationstype').val(data.type);
            $('#User_notificationsstatus').val(data.status);
            //$('#User_notificationstime_readed').val(data.time_readed);
            $('#User_notificationsusers_id').val(data.users_id);
            $('#User_notificationsimage').val(data.image);
            $('#User_notificationsicon').val(data.icon);
            $("select[name='User_notificationsicon']").val(data.icon);
            $("select[name='User_notificationsicon']").trigger('change');
            $('#User_notificationshref').val(data.href);
            $('#User_notificationsonclick').val(data.onclick);
            $('#User_notificationselement_class').val(data.element_class);
            $('#User_notificationselement_id').val(data.element_id);
            $('#User_notificationspriority').val(data.priority);
<?php echo $autocomplete; ?>
        });
    });
</script>
<script> $(document).ready(function () {
        $('#User_notificationstime_readed').datetimepicker({format: 'yyyy-mm-dd hh:ii', autoclose: true});
    });</script>