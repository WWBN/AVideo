<?php
if (!isset($global['systemRootPath'])) {
    $configFile = '../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

if (!User::canCreateMeet()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$userCredentials = User::loginFromRequestToGet();
if (empty($meet_scheduled)) {
    $meet_scheduled = cleanString($_REQUEST['meet_scheduled']);
}

if (empty($manageMeetings)) {
    $manageMeetings = intval($_REQUEST['manageMeetings']);
}
?>

<table id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>
                <?php
                if ($manageMeetings) {
                    ?>
                    <button class="btn btn-danger btn-xs deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?> disabled" data-toggle="tooltip" title="<?php echo __("Delete All Selected"); ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php
                }
                ?>
            </th>
            <th><?php echo __("Topic"); ?></th>
            <th><?php echo __("Starts"); ?></th>
            <th><?php echo __("Owner"); ?></th>
            <th></th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th>
                <?php
                if ($manageMeetings) {
                    ?>
                    <button class="btn btn-danger btn-xs deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?> disabled" data-toggle="tooltip" title="<?php echo __("Delete All Selected"); ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?php
                }
                ?>
            </th>
            <th><?php echo __("Topic"); ?></th>
            <th><?php echo __("Starts"); ?></th>
            <th><?php echo __("Owner"); ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
<div id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <?php
        if ($meet_scheduled == "today") {
            ?>
            <button href="" class="go_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-success btn-xs" 
                    data-toggle="tooltip" title="<?php echo __("Join"); ?>">
                <i class="fa fa-check"></i>
            </button>
            <?php
        }
        if ($meet_scheduled == "today" || $meet_scheduled == "upcoming") {
            ?>
            <button href="" class="copyInvitation_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-default btn-xs" 
                    data-toggle="tooltip" title="<?php echo __("Copy Invitation"); ?>">
                <i class="fa fa-copy"></i>
            </button>
            <button href="" class="copyLink_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-default btn-xs" 
                    data-toggle="tooltip" title="<?php echo __("Copy Link"); ?>">
                <i class="fa fa-link"></i>
            </button>
            <?php
        }
        ?>
        <?php
        if ($manageMeetings) {
            ?>
            <button href="" class="log_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-primary btn-xs"
                    data-toggle="tooltip" title="<?php echo __("Meet Log"); ?>">
                <i class="fas fa-info-circle"></i>
            </button>
            <button href="" class="edit_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-default btn-xs"
                    data-toggle="tooltip" title="<?php echo __("Edit"); ?>">
                <i class="fa fa-edit"></i>
            </button>
            <button href="" class="delete_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-danger btn-xs"
                    data-toggle="tooltip" title="<?php echo __("Delete"); ?>">
                <i class="fa fa-trash"></i>
            </button>
            <?php
        }
        ?>
    </div>
</div>
<div id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" style="max-height: 90vh; overflow-y: auto;">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script type="text/javascript">
    var Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar;
    $(document).ready(function () {
        Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar = $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').DataTable({

            "processing": true,
            "serverSide": true,
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/list.json.php?meet_scheduled=<?php echo $meet_scheduled; ?>&manageMeetings=<?php echo $manageMeetings; ?>&<?php echo $userCredentials; ?>",
                        "order": [],
                        "columns": [
                            {
                                /**
                                 * Public = 2
                                 * Logged Users Only = 1
                                 * Specific User Groups = 0
                                 * @return type
                                 */
                                sortable: false,
                                data: 'public',
                                "render": function (data, type, row) {
                                    var checkbox = "<input type=\"checkbox\" value=\"" + row.id + "\" class=\" Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>\"> ";
                                    if (data == 2) {
                                        return checkbox + '<i class="fas fa-lock-open" style="color:rgba(0,0,0,0.1);" data-toggle="tooltip" title="<?php echo __("Public"); ?>" ></i>';
                                    } else if (data == 1) {
                                        return checkbox + '<i class="fas fa-user-lock" style="color:rgba(0,0,0,0.3);" data-toggle="tooltip" title="<?php echo __("Logged Users Only"); ?>" ></i>'
                                    } else {
                                        return checkbox + '<i class="fas fa-lock" style="color:rgba(0,0,0,1);" data-toggle="tooltip" title="<?php echo __("Specific User Groups"); ?>" ></i>'
                                    }
                                }
                            },
                            {"data": "topic"},
                            {"data": "starts"},
                            {"data": "identification"},
                            {
                                sortable: false,
                                data: null,
                                defaultContent: $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinks').html()
                            }
                        ],
                        select: true,
                        "initComplete": function (settings, json) {
                            $('[data-toggle="tooltip"]').tooltip({container: 'body'});
                        }
                    });
<?php
if ($manageMeetings) {
    ?>
                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.delete_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                            e.preventDefault();
                            var tr = $(this).closest('tr')[0];
                            var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
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
                                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/delete.json.php?<?php echo $userCredentials; ?>",
                                                                                data: data

                                                                            }).done(function (resposta) {
                                                                                if (resposta.error) {
                                                                                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                                                                }
                                                                                Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.ajax.reload();
                                                                                modal.hidePleaseWait();
                                                                            });
                                                                        } else {

                                                                        }
                                                                    });
                                                        });
                                                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.log_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                            e.preventDefault();
                                                            var tr = $(this).closest('tr')[0];
                                                            var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
                                                            modal.showPleaseWait();
                                                            $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal').modal();
                                                            $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal .modal-body').html('');
                                                            $.ajax({
                                                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/getMeetInfo.json.php?meet_scheduled=<?php echo $meet_scheduled; ?>&meet_schedule_id=' + data.id + '&<?php echo $userCredentials; ?>',
                                                                success: function (response) {
                                                                    if (response.error) {
                                                                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                                    } else {
                                                                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal .modal-body').html(response.html);
                                                                    }
                                                                    modal.hidePleaseWait();
                                                                }
                                                            });
                                                        });
                                                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.edit_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                            e.preventDefault();
                                                            var tr = $(this).closest('tr')[0];
                                                            var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();

                                                            clearMeetForm(false);

                                                            $('#meet_schedule_id').val(data.id);
                                                            $('#RoomTopic').val(data.topic);
                                                            $('#RoomPasswordNew').val(data.password);
                                                            $('#live_streamNew').val(data.live_stream);
                                                            $('#publicNew').val(data.public);
                                                            $('#whenNew').val(0);
                                                            $('#Meet_schedule2starts').val(data.starts);

                                                            if (data.userGroups) {
                                                                for (i = 0; i < data.userGroups.length; i++) {
                                                                    $('#userGroupsCheck' + data.userGroups[i].users_groups_id).attr('checked', 1);
                                                                }
                                                            }
                                                            $('#publicNew, #whenNew').trigger("change");

                                                        });
    <?php
}
?>
                                                    $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.go_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                        e.preventDefault();
                                                        modal.showPleaseWait();
                                                        var tr = $(this).closest('tr')[0];
                                                        var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
                                                        document.location = data.link;

                                                    });
                                                    $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.copyInvitation_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                        e.preventDefault();
                                                        var tr = $(this).closest('tr')[0];
                                                        var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
                                                        copyToClipboard(data.invitation);
                                                    });
                                                    $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.copyLink_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                        e.preventDefault();
                                                        var tr = $(this).closest('tr')[0];
                                                        var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
                                                        copyToClipboard(data.link);
                                                    });

                                                    $('.deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?>').click(function (e) {
                                                        if (!$("input.Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>:checked").length) {
                                                            return false;
                                                        }
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
                                                                        var array = []
                                                                        $("input.Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>:checked").each(function () {
                                                                            array.push($(this).val());
                                                                        });
                                                                        $.ajax({
                                                                            type: "POST",
                                                                            url: webSiteRootURL + "plugin/Meet/View/Meet_schedule/delete.json.php?<?php echo $userCredentials; ?>",
                                                                            data: {id: array}

                                                                        }).done(function (resposta) {
                                                                            if (resposta.error) {
                                                                                avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                                                            }
                                                                            Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.ajax.reload();
                                                                            modal.hidePleaseWait();
                                                                        });
                                                                    } else {

                                                                    }
                                                                });



                                                    });
                                                    $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'input.Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
                                                        if ($("input.Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>:checked").length) {
                                                            $('.deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?>').removeClass('disabled');
                                                        } else {
                                                            $('.deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?>').addClass('disabled');
                                                        }


                                                    });
                                                    setTimeout(function () {
                                                        $('[data-toggle="tooltip"]').tooltip({container: 'body'});
                                                    }, 500);
                                                });
</script>