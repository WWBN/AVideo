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
                        <form id="panelEmails_messagesForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Emails_messagesid" value="">
                                <div class="form-group col-sm-12">
                                    <label for="Emails_messagesmessage"><?php echo __("Message"); ?>:</label>
                                    <textarea id="Emails_messagesmessage" name="message" class="form-control input-sm" placeholder="<?php echo __("Message"); ?>" required="true"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Emails_messagessubject"><?php echo __("Subject"); ?>:</label>
                                    <input type="text" id="Emails_messagessubject" name="subject" class="form-control input-sm" placeholder="<?php echo __("Subject"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newEmails_messagesLink" onclick="clearEmails_messagesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Emails_messagesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Message"); ?></th>
                                    <th><?php echo __("Subject"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Message"); ?></th>
                                    <th><?php echo __("Subject"); ?></th>
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
<div id="Emails_messagesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Emails_messages btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Emails_messages btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearEmails_messagesForm() {
        $('#Emails_messagesid').val('');
        $('#Emails_messagesmessage').val('');
        $('#Emails_messagessubject').val('');
    }
    $(document).ready(function() {
        $('#addEmails_messagesBtn').click(function() {
            $.ajax({
                url: webSiteRootURL+'plugin/Scheduler/View/addEmails_messagesVideo.php',
                data: $('#panelEmails_messagesForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelEmails_messagesForm").trigger("reset");
                    }
                    clearEmails_messagesForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Emails_messagestableVar = $('#Emails_messagesTable').DataTable({
            serverSide: true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Emails_messages/list.json.php",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "message"
                },
                {
                    "data": "subject"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Emails_messagesbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newEmails_messages').on('click', function(e) {
            e.preventDefault();
            $('#panelEmails_messagesForm').trigger("reset");
            $('#Emails_messagesid').val('');
        });
        $('#panelEmails_messagesForm').on('submit', function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/Scheduler/View/Emails_messages/add.json.php',
                data: $('#panelEmails_messagesForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelEmails_messagesForm").trigger("reset");
                    }
                    Emails_messagestableVar.ajax.reload();
                    $('#Emails_messagesid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Emails_messagesTable').on('click', 'button.delete_Emails_messages', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Emails_messagestableVar.row(tr).data();
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
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/Scheduler/View/Emails_messages/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlertError(resposta.msg);
                            }
                            Emails_messagestableVar.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#Emails_messagesTable').on('click', 'button.edit_Emails_messages', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Emails_messagestableVar.row(tr).data();
            $('#Emails_messagesid').val(data.id);
            $('#Emails_messagesmessage').val(data.message);
            $('#Emails_messagessubject').val(data.subject);
        });
    });
</script>