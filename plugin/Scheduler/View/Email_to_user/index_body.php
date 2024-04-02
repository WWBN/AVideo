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
                        <form id="panelEmail_to_userForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Email_to_userid" value="">
                                <div class="form-group col-sm-12">
                                    <label for="Email_to_usersent_at"><?php echo __("Sent At"); ?>:</label>
                                    <input type="text" id="Email_to_usersent_at" name="sent_at" class="form-control input-sm" placeholder="<?php echo __("Sent At"); ?>" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Email_to_usertimezone"><?php echo __("Timezone"); ?>:</label>
                                    <input type="text" id="Email_to_usertimezone" name="timezone" class="form-control input-sm" placeholder="<?php echo __("Timezone"); ?>">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Email_to_useremails_messages_id"><?php echo __("Emails Messages Id"); ?>:</label>
                                    <select class="form-control input-sm" name="emails_messages_id" id="Email_to_useremails_messages_id">
                                        <?php
                                        $options = Email_to_user::getAllEmails_messages();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Email_to_userusers_id"><?php echo __("Users Id"); ?>:</label>
                                    <select class="form-control input-sm" name="users_id" id="Email_to_userusers_id">
                                        <?php
                                        $options = User::getAllUsers();
                                        foreach ($options as $value) {
                                            echo '<option value="' . $value['id'] . '">' . $value['id'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newEmail_to_userLink" onclick="clearEmail_to_userForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Email_to_userTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Sent At"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Sent At"); ?></th>
                                    <th><?php echo __("Timezone"); ?></th>
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
<div id="Email_to_userbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Email_to_user btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Email_to_user btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearEmail_to_userForm() {
        $('#Email_to_userid').val('');
        $('#Email_to_usersent_at').val('');
        $('#Email_to_usertimezone').val('');
        $('#Email_to_useremails_messages_id').val('');
        $('#Email_to_userusers_id').val('');
    }
    $(document).ready(function() {
        $('#addEmail_to_userBtn').click(function() {
            $.ajax({
                url: webSiteRootURL+'plugin/Scheduler/View/addEmail_to_userVideo.php',
                data: $('#panelEmail_to_userForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelEmail_to_userForm").trigger("reset");
                    }
                    clearEmail_to_userForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Email_to_usertableVar = $('#Email_to_userTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Email_to_user/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "sent_at"
                },
                {
                    "data": "timezone"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Email_to_userbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newEmail_to_user').on('click', function(e) {
            e.preventDefault();
            $('#panelEmail_to_userForm').trigger("reset");
            $('#Email_to_userid').val('');
        });
        $('#panelEmail_to_userForm').on('submit', function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/Scheduler/View/Email_to_user/add.json.php',
                data: $('#panelEmail_to_userForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelEmail_to_userForm").trigger("reset");
                    }
                    Email_to_usertableVar.ajax.reload();
                    $('#Email_to_userid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Email_to_userTable').on('click', 'button.delete_Email_to_user', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Email_to_usertableVar.row(tr).data();
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
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Email_to_user/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            }
                            Email_to_usertableVar.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#Email_to_userTable').on('click', 'button.edit_Email_to_user', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Email_to_usertableVar.row(tr).data();
            $('#Email_to_userid').val(data.id);
            $('#Email_to_usersent_at').val(data.sent_at);
            $('#Email_to_usertimezone').val(data.timezone);
            $('#Email_to_useremails_messages_id').val(data.emails_messages_id);
            $('#Email_to_userusers_id').val(data.users_id);
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#Email_to_usersent_at').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true
        });
    });
</script>