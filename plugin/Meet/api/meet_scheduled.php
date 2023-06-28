<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($global['systemRootPath'])) {
    $configFile = '../../../videos/configuration.php';
    if (file_exists($configFile)) {
        require_once $configFile;
    }
}

$obj = AVideoPlugin::getObjectDataIfEnabled("Meet");
//_error_log(json_encode($_SERVER));
if (empty($obj)) {
    die("Plugin disabled");
}

$userCredentials = User::loginFromRequestToGet();

if (empty($meet_scheduled)) {
    $meet_scheduled = cleanString($_REQUEST['meet_scheduled']);
}

if (empty($manageMeetings)) {
    $manageMeetings = intval($_REQUEST['manageMeetings']);
}
if (!User::canCreateMeet()) {
    $manageMeetings = false;
}

$end_meet_redirect = $_REQUEST['end_meet_redirect'];

?>

<table id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>
                <?php
                if ($manageMeetings) {
                    ?>
                    <button class="btn btn-danger btn-xs deleteSelectedMeet<?php echo $meet_scheduled, $manageMeetings; ?> disabled" data-toggle="tooltip" title="<?php echo __("Delete All Selected"); ?>">
                        <i class="fa fa-trash"></i>
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
    <!-- <tfoot>
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
    </tfoot> -->
</table>
<div id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinksJoinOnly" style="display:none;">
    <div class="btn-group pull-right">
        <?php
        if ($meet_scheduled == "today") {
            ?>
            <button href="" class="go_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?> btn btn-success btn-xs"
                    data-toggle="tooltip" title="<?php echo __("Join"); ?>">
                <i class="fa fa-check"></i>
            </button>
            <?php
        }else{
            echo __("Comming Soon");
        }
        ?>
    </div>
</div>
<div id="Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinks" style="display:none;">
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
                <i class="fa fa-info-circle"></i>
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
            "serverSide": false, // updated to false julz
            xhrFields: {
                withCredentials: false
            },
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Meet/View/Meet_schedule/list.json.php?meet_scheduled=<?php echo $meet_scheduled; ?>&manageMeetings=<?php echo $manageMeetings; ?>&<?php echo $userCredentials; ?>",
                        "order": [[ 2, 'desc' ]],
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
                                    var manageMeetings = "<?php echo $manageMeetings ?>"
                                    if (manageMeetings > 0 || manageMeetings == true) {
                                        var checkbox = "<input type=\"checkbox\" value=\"" + row.id + "\" class=\" Meet_checkbox<?php echo $meet_scheduled, $manageMeetings; ?>\"> ";
                                    } else {
                                        var checkbox = "";
                                    }
                                    
                                    if (data == 2) {
                                        return checkbox + '<i class="fa fa-unlock" style="color:rgba(0,0,0,0.1);" data-toggle="tooltip" title="<?php echo __("Public"); ?>" ></i>';
                                    } else if (data == 1) {
                                        return checkbox + '<span data-toggle="tooltip" title="<?php echo __("Logged Users Only"); ?>"><i class="fa fa-user" style="color:rgba(0,0,0,0.3);"></i> \
                                                            <i class="fa fa-lock" style="color:rgba(0,0,0,0.3);"></i></span>'
                                    } else {
                                        return checkbox + '<i class="fa fa-lock" style="color:rgba(0,0,0,1);" data-toggle="tooltip" title="<?php echo __("Specific User Groups"); ?>" ></i>'
                                    }
                                }
                            },
                            {"data": "topic"},
                            {"data": "starts"},
                            {"data": "identification"},
                            {
                                sortable: false,
                                data: null,
                                "render": function (data, type, row) {

                                    if(row.isModerator){
                                        return $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinks').html()
                                    }else{
                                        return $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>btnModelLinksJoinOnly').html()

                                    }
                                }
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
                                                            
                                                            $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal .modal-body').html('');
                                                            $.ajax({
                                                                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Meet/api/getMeetInfo.json.php?meet_scheduled=<?php echo $meet_scheduled; ?>&meet_schedule_id=' + data.id + '&<?php echo $userCredentials; ?>',
                                                                data: {domain: "<?php echo $_REQUEST['domain'] ?>"},
                                                                success: function (response) {
                                                                    modal.hidePleaseWait();
                                                                    if (response.error) {
                                                                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                                                                    } else {
                                                                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal .modal-body').html(response.html);
                                                                    }
                                                                    setTimeout(() => {
                                                                        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Modal').modal();
                                                                    }, 1000);
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
            // document.location = data.link;
            // console.log(data.link)
            var link = data.link.replace("<?php echo $global['webSiteRootURL'] ?>", "<?php echo $_REQUEST['domain'] ?>/");
            link = link + "?redirect=<?php echo $end_meet_redirect ?>";
            modal.hidePleaseWait();
            window.open(link, '_self');

        });
        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.copyInvitation_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
            // copyToClipboard(data.invitation);
            var link = data.link.replace("<?php echo $global['webSiteRootURL'] ?>", "<?php echo $_REQUEST['domain'] ?>/"); // + "?redirect=<? //php echo $end_meet_redirect ?>";
            copyToClipboard(data.invitation.replace(data.link, link))
        });
        $('#Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>Table').on('click', 'button.copyLink_Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Meet_schedule2<?php echo $meet_scheduled, $manageMeetings; ?>tableVar.row(tr).data();
            // copyToClipboard(data.link);
            var link = data.link.replace("<?php echo $global['webSiteRootURL'] ?>", "<?php echo $_REQUEST['domain'] ?>/"); // + "?redirect=<?php //echo $end_meet_redirect ?>"; 
            copyToClipboard(link)
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

    function clearMeetForm(triggerChange) {
        $('#meet_schedule_id').val('');
        $('#RoomTopic').val('');
        $('#RoomPasswordNew').val('');
        $('#live_streamNew').val('');
        $('#publicNew').val('2');
        $('input.userGroups:checkbox').removeAttr('checked');
        $('#whenNew').val('1');
        $('#Meet_schedule2starts').val('');
        $('#formMeetManager')[0].reset();
        if (triggerChange) {
            $('#publicNew, #whenNew').trigger("change");
        }
    }
</script>