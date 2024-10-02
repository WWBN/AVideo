<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!Live::canRestream()) {
    return false;
}
?>
<link rel="stylesheet" type="text/css" href="<?php echo getURL('view/css/DataTables/datatables.min.css'); ?>"/>
<div class="row">
    <div class="col-sm-12  <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'restream', 0.1); ?>">
        <form id="panelLive_restreamsForm">
            <div class="row">
                <input type="hidden" name="id" id="Live_restreamsid" value="" >
                <div class="form-group col-sm-6" id="Live_restreamsnameDiv">
                    <label for="Live_restreamsname"><?php echo __("Name"); ?>:</label>
                    <input type="text" id="Live_restreamsname" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                </div>
                <div class="form-group col-sm-6" id="Live_restreamsstatusDiv">
                    <label for="status"><?php echo __("Status"); ?>:</label>
                    <select class="form-control input-sm" name="status" id="Live_restreamsstatus">
                        <option value="a"><?php echo __("Active"); ?></option>
                        <option value="i"><?php echo __("Inactive"); ?></option>
                    </select>
                </div>
                <div class="form-group col-sm-6" id="Live_restreamsstream_urlDiv">
                    <label for="Live_restreamsstream_url"><?php echo __("Stream Url"); ?>:</label>
                    <input type="text" id="Live_restreamsstream_url" name="stream_url" class="form-control input-sm" placeholder="<?php echo __("Stream Url"); ?>" required="true">
                </div>
                <div class="form-group col-sm-6" id="Live_restreamsstream_keyDiv">
                    <label for="Live_restreamsstream_key"><?php echo __("Stream Key"); ?>:</label>
                    <input type="text" id="Live_restreamsstream_key" name="stream_key" class="form-control input-sm" placeholder="<?php echo __("Stream Key"); ?>" required="true">
                </div>
                <div class="form-group col-sm-12 hidden">
                    <label for="Live_restreamsparameters"><?php echo __("Parameters"); ?>:</label>
                    <textarea id="Live_restreamsparameters" name="parameters" class="form-control input-sm" placeholder="<?php echo __("Parameters"); ?>"></textarea>
                </div>

                <div class="form-group col-sm-12 ">
                    <div class="btn-group pull-right">
                        <span class="btn btn-success" id="newLive_restreamsLink" onclick="clearLive_restreamsForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                        <button class="btn btn-primary" type="submit" id="saveLive_restreamsLink" ><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="col-sm-12  <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'restream', 0.1); ?>">
        <table id="Live_restreamsTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php echo __("Name"); ?></th>
                    <th><?php echo __("Status"); ?></th>
                    <th><?php echo __("Key"); ?></th>
                    <th><?php echo __("Valid"); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>#</th>
                    <th><?php echo __("Name"); ?></th>
                    <th><?php echo __("Status"); ?></th>
                    <th><?php echo __("Key"); ?></th>
                    <th><?php echo __("Valid"); ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div id="Live_restreamsbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button class="edit_Live_restreams btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button class="delete_Live_restreams btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
<script type="text/javascript" src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#addLive_restreamsBtn').click(function () {
            $.ajax({
                url: webSiteRootURL + 'plugin/Live/view/addLive_restreamsVideo.php',
                data: $('#panelLive_restreamsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelLive_restreamsForm").trigger("reset");
                    }
                    clearLive_restreamsForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Live_restreamstableVar = $('#Live_restreamsTable').DataTable({
            "ajax": webSiteRootURL + "plugin/Live/view/Live_restreams/list.json.php?users_id=<?php echo User::getId(); ?>",
            "columns": [
                {"data": "id"},
                {
                    "data": "display_name",
                    render: function (data, type, row) {
                        //console.log('Live_restreamstableVar row', row.parameters);
                        if (!empty(row.parameters)) {
                            var json = JSON.parse(row.parameters);
                            //console.log('Live_restreamstableVar parameters', json);
                            if (!empty(json['restream.ypt.me'])) {
                                return '<a href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_restreams/getLiveKey.json.php?live_restreams_id=' + row.id + '" target="_blank" style="color:#000;">' + data + '</a>';
                            }

                        }
                        return data;
                    }
                },
                {"data": "status"},
                {"data": "stream_key_short"},
                {"data": "revalidateButton"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Live_restreamsbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newLive_restreams').on('click', function (e) {
            e.preventDefault();
            $('#panelLive_restreamsForm').trigger("reset");
            $('#Live_restreamsid').val('');
        });
        $('#panelLive_restreamsForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/Live/view/Live_restreams/add.json.php',
                data: $('#panelLive_restreamsForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        avideoToastSuccess(response.msg);
                        $("#panelLive_restreamsForm").trigger("reset");
                    }
                    Live_restreamstableVar.ajax.reload();
                    $('#Live_restreamsid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Live_restreamsTable').on('click', 'button.delete_Live_restreams', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_restreamstableVar.row(tr).data();
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
                                url: webSiteRootURL + "plugin/Live/view/Live_restreams/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Live_restreamstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Live_restreamsTable').on('click', 'button.edit_Live_restreams', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_restreamstableVar.row(tr).data();
            $('#Live_restreamsid').val(data.id);
            $('#Live_restreamsname').val(data.name);
            $('#Live_restreamsstream_url').val(data.stream_url);
            $('#Live_restreamsstream_key').val(data.stream_key);
            $('#Live_restreamsstatus').val(data.status);
            $('#Live_restreamsparameters').val(data.parameters);
            $('#Live_restreamsusers_id').val(data.users_id);
        });
    });

    function clearLive_restreamsForm() {
        $('#Live_restreamsid').val('');
        $('#Live_restreamsname').val('');
        $('#Live_restreamsstream_url').val('');
        $('#Live_restreamsstream_key').val('');
        $('#Live_restreamsstatus').val('');
        $('#Live_restreamsparameters').val('');
        $('#Live_restreamsusers_id').val('');
    }
</script>
