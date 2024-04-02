<?php
require_once '../../../../videos/configuration.php';

if (empty($_REQUEST['program_id'])) {
    forbiddenPage(__("program_id is required"));
}

$obj = AVideoPlugin::getObjectDataIfEnabled("PlayLists");
if (empty($obj)) {
    forbiddenPage(__("The plugin is disabled"));
}

if (!AVideoPlugin::isEnabledByName("Rebroadcaster")) {
    forbiddenPage(__("Rebroadcaster plugin is required"));
}

if (!User::canStream()) {
    forbiddenPage(__("You cannot livestream"));
}

$pl = new PlayList($_REQUEST['program_id']);
//var_dump($pl->getName());exit;
//$videosArrayId = PlayList::getVideosIdFromPlaylist($_REQUEST['program_id']);
//$videosP = Video::getAllVideos(Video::SORT_TYPE_VIEWABLE, false, true, $videosArrayId, false, true);
//var_dump( $videosP);exit;
$_page = new Page(array('Schedule Playlist'));
$_page->setExtraStyles(
    array(
        'view/css/DataTables/datatables.min.css',
    )
);
$_page->setExtraScripts(
    array(
        'view/css/DataTables/datatables.min.js',
    )
);
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-4">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                    <div class="panel-body">
                        <form id="panelPlaylists_schedulesForm">
                            <input type="hidden" name="playlists_id" id="Playlists_schedulesplaylists_id" value="<?php echo $_REQUEST['program_id']; ?>" />
                            <div class="row">
                                <input type="hidden" name="id" id="Playlists_schedulesid" value="">
                                <div class="form-group col-sm-12">
                                    <label for="Playlists_schedulesname"><?php echo __("Name"); ?>:</label>
                                    <input type="text" id="Playlists_schedulesname" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true" value="<?php echo $pl->getName(); ?>">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="Playlists_schedulesdescription"><?php echo __("Description"); ?>:</label>
                                    <textarea id="Playlists_schedulesdescription" name="description" class="form-control input-sm" placeholder="<?php echo __("Description"); ?>"></textarea>
                                </div>
                                <div class="form-group col-sm-4" id="group-start">
                                    <label for="Playlists_schedulesstart_datetime"><?php echo __("Start"); ?>:</label>
                                    <input type="datetime-local" id="Playlists_schedulesstart_datetime" name="start_datetime" class="form-control input-sm" placeholder="Start Datetime" required="true" autocomplete="off">
                                </div>
                                <div class="form-group col-sm-4" id="group-finish">
                                    <label for="Playlists_schedulesfinish_datetime"><?php echo __("Finish"); ?>:</label>
                                    <input type="datetime-local" id="Playlists_schedulesfinish_datetime" name="finish_datetime" class="form-control input-sm" placeholder="Finish Datetime" required="true" autocomplete="off">
                                    <span class="help-block" id="error-finish" style="display: none;"><?php echo __("Finish Datetime must be greater than Start Datetime"); ?>.</span>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="Playlists_schedulesloop"><?php echo __("Loop"); ?>:</label>
                                    <select class="form-control input-sm" name="loop" id="Playlists_schedulesloop">
                                        <option value="1"><?php echo __("Yes"); ?></option>
                                        <option value="0"><?php echo __("No"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-6 hidden">
                                    <label for="Playlists_schedulesrepeat "><?php echo __("Repeat"); ?>:</label>
                                    <select class="form-control input-sm" name="repeat" id="Playlists_schedulesrepeat">
                                        <option value="<?php echo Playlists_schedules::$REPEAT_NEVER; ?>"><?php echo __("Never"); ?></option>
                                        <option value="<?php echo Playlists_schedules::$REPEAT_DAILY; ?>"><?php echo __("Daily"); ?></option>
                                        <option value="<?php echo Playlists_schedules::$REPEAT_WEEKLY; ?>"><?php echo __("Weekly"); ?></option>
                                        <option value="<?php echo Playlists_schedules::$REPEAT_MONTHLY; ?>"><?php echo __("Monthly"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12" style="display: none;">
                                    <label for="Playlists_schedulesparameters"><?php echo __("Parameters"); ?>:</label>
                                    <textarea id="Playlists_schedulesparameters" name="parameters" class="form-control input-sm" placeholder="<?php echo __("Parameters"); ?>"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newPlaylists_schedulesLink" onclick="clearPlaylists_schedulesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Playlists_schedulesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Loop"); ?></th>
                                    <th><?php echo __("Start Datetime"); ?></th>
                                    <th><?php echo __("Finish Datetime"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Loop"); ?></th>
                                    <th><?php echo __("Start Datetime"); ?></th>
                                    <th><?php echo __("Finish Datetime"); ?></th>
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
<div id="Playlists_schedulesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Playlists_schedules btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Playlists_schedules btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearPlaylists_schedulesForm() {
        $('#Playlists_schedulesid').val('');
        $('#Playlists_schedulesplaylists_id').val('');
        $('#Playlists_schedulesname').val('');
        $('#Playlists_schedulesdescription').val('');
        $('#Playlists_schedulesstatus').val('');
        $('#Playlists_schedulesloop').val('');
        $('#Playlists_schedulesstart_datetime').val('');
        $('#Playlists_schedulesfinish_datetime').val('');
        $('#Playlists_schedulesrepeat').val('');
        $('#Playlists_schedulesparameters').val('');
    }
    $(document).ready(function() {
        $('#addPlaylists_schedulesBtn').click(function() {
            $.ajax({
                url: webSiteRootURL + 'plugin/PlayLists/View/Playlists_schedules/add.json.php',
                data: $('#panelPlaylists_schedulesForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToastSuccess(response.msg);
                        $("#panelPlaylists_schedulesForm").trigger("reset");
                    }
                    clearPlaylists_schedulesForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Playlists_schedulestableVar = $('#Playlists_schedulesTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/View/Playlists_schedules/list.json.php?program_id=<?php echo intval($_REQUEST['program_id']); ?>",
            "columns": [{
                    "data": "id"
                },
                {
                    "data": "name"
                },
                {
                    "data": "status",
                    "render": function(data, type, row) {
                        console.log(data, type, row);
                        return row.statusText;
                    }
                },
                {
                    "data": "loop",
                    "render": function(data, type, row) {
                        return row.loopText;
                    }
                },
                {
                    "data": "start_datetime"
                },
                {
                    "data": "finish_datetime"
                },
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Playlists_schedulesbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newPlaylists_schedules').on('click', function(e) {
            e.preventDefault();
            $('#panelPlaylists_schedulesForm').trigger("reset");
            $('#Playlists_schedulesid').val('');
        });
        $('#panelPlaylists_schedulesForm').on('submit', function(e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL+'plugin/PlayLists/View/Playlists_schedules/add.json.php',
                data: $('#panelPlaylists_schedulesForm').serialize(),
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelPlaylists_schedulesForm").trigger("reset");
                    }
                    Playlists_schedulestableVar.ajax.reload();
                    $('#Playlists_schedulesid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Playlists_schedulesTable').on('click', 'button.delete_Playlists_schedules', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Playlists_schedulestableVar.row(tr).data();
            swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/PlayLists/View/Playlists_schedules/delete.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                            }
                            Playlists_schedulestableVar.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    } else {

                    }
                });
        });
        $('#Playlists_schedulesTable').on('click', 'button.edit_Playlists_schedules', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Playlists_schedulestableVar.row(tr).data();
            $('#Playlists_schedulesid').val(data.id);
            $('#Playlists_schedulesplaylists_id').val(data.playlists_id);
            $('#Playlists_schedulesname').val(data.name);
            $('#Playlists_schedulesdescription').val(data.description);
            $('#Playlists_schedulesstatus').val(data.status);
            $('#Playlists_schedulesloop').val(data.loop);
            $('#Playlists_schedulesstart_datetime').val(data.start_datetime);
            $('#Playlists_schedulesfinish_datetime').val(data.finish_datetime);
            $('#Playlists_schedulesrepeat').val(data.repeat);
            $('#Playlists_schedulesparameters').val(data.parameters);
        });

        $('#Playlists_schedulesstart_datetime, #Playlists_schedulesfinish_datetime').change(function() {
            var start = new Date($('#Playlists_schedulesstart_datetime').val());
            var finish = new Date($('#Playlists_schedulesfinish_datetime').val());

            if (start != 'Invalid Date' && finish != 'Invalid Date' && start >= finish) {
                $('#group-finish').addClass('has-error');
                $('#error-finish').show();
            } else {
                $('#group-finish').removeClass('has-error');
                $('#error-finish').hide();
            }
        });
    });
</script>

<?php
$_page->print();
?>