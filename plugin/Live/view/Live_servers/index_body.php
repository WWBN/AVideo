<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
$obj = AVideoPlugin::getObjectData("Live");
?>
<style>
    #panelLive_serversForm div{
        min-height: 50px;
    }
</style>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fas fa-cog"></i> <?php echo __("Configurations"); ?>
    </div>
    <div class="panel-body">
        <?php
        if(empty($obj->useLiveServers)){
        ?>
        <div class="alert alert-danger">
            Live Servers is Disabled, if you want to use it, enable it on the (Live) Plugin
        </div>
        <?php
        }
        ?>
        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default ">
                    <div class="panel-heading"><i class="far fa-plus-square"></i> <?php echo __("Create"); ?></div>
                    <div class="panel-body">
                        <form id="panelLive_serversForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Live_serversid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Live_serversname"><?php echo __("Name"); ?>:</label>
                                    <input type="text" id="Live_serversname" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serversrtmp_server"><?php echo __("Rtmp Server"); ?>:</label>
                                    <input type="url" id="Live_serversrtmp_server" name="rtmp_server" class="form-control input-sm" placeholder="<?php echo __("Rtmp Server"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serversplayerServer"><?php echo __("Player Server"); ?>:</label>
                                    <input type="url" id="Live_serversplayerServer" name="playerServer" class="form-control input-sm" placeholder="<?php echo __("PlayerServer"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serversstats_url"><?php echo __("Stats Url"); ?>:</label>
                                    <input type="url" id="Live_serversstats_url" name="stats_url" class="form-control input-sm" placeholder="<?php echo __("Stats Url"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serversgetRemoteFile"><?php echo __("Get Remote File URL"); ?>:</label>
                                    <input type="url" id="Live_serversgetRemoteFile" name="getRemoteFile" class="form-control input-sm" placeholder="<?php echo __("GetRemoteFile"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serversrestreamerURL"><?php echo __("Restreamer URL"); ?>:</label>
                                    <input type="url" id="Live_serversrestreamerURL" name="restreamerURL" class="form-control input-sm" placeholder="<?php echo __("Restreamer URL"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Live_serverscontrolURL"><?php echo __("Control URL"); ?>:</label>
                                    <input type="url" id="Live_serverscontrolURL" name="controlURL" class="form-control input-sm" placeholder="<?php echo __("Control URL"); ?>" required="true">
                                </div>

                                <div class="form-group col-sm-4">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Live_serversstatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="Live_serversdisableDVR"><?php echo __("DisableDVR"); ?>:</label>
                                    <select class="form-control input-sm" id="Live_serversdisableDVR" name="disableDVR" >
                                        <option value="0"><?php echo __("No"); ?></option>
                                        <option value="1"><?php echo __("Yes"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="Live_serversdisableGifThumbs"><?php echo __("DisableGifThumbs"); ?>:</label>
                                    <select class="form-control input-sm" id="Live_serversdisableGifThumbs" name="disableGifThumbs"  >
                                        <option value="0"><?php echo __("No"); ?></option>
                                        <option value="1"><?php echo __("Yes"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="Live_serversuseAadaptiveMode"><?php echo __("UseAadaptiveMode"); ?>:</label>
                                    <select class="form-control input-sm"  id="Live_serversuseAadaptiveMode" name="useAadaptiveMode"  >
                                        <option value="0"><?php echo __("No"); ?></option>
                                        <option value="1"><?php echo __("Yes"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label for="Live_serversprotectLive"><?php echo __("ProtectLive"); ?>:</label>
                                    <select class="form-control input-sm" id="Live_serversprotectLive" name="protectLive">
                                        <option value="0"><?php echo __("No"); ?></option>
                                        <option value="1"><?php echo __("Yes"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newLive_servers"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Live_serversTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("RTMP"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Name"); ?></th>
                                    <th><?php echo __("RTMP"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="alert alert-info">
                            Make sure your nginx.conf has the following parameters
                            <hr>
                            <code>
                                
                            on_publish <?php echo str_replace("https:", "http:", $global['webSiteRootURL']); ?>plugin/Live/on_publish.php;<br>
                            on_play <?php echo str_replace("https:", "http:", $global['webSiteRootURL']); ?>plugin/Live/on_play.php;<br>
                            on_record_done <?php echo str_replace("https:", "http:", $global['webSiteRootURL']); ?>plugin/Live/on_record_done.php;<br>

                            </code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="Live_serversbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="go_Live_servers btn btn-primary btn-xs">
            <i class="fa fa-circle"></i>
        </button>
        <button href="" class="edit_Live_servers btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Live_servers btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearLive_serversForm() {
        $('#Live_serversid').val('');
        $('#Live_serversname').val('');
        $('#Live_serversurl').val('');
        $('#Live_serversstatus').val('');
        $('#Live_serversrtmp_server').val('');
        $('#Live_serversplayerServer').val('');
        $('#Live_serversstats_url').val('');
        $('#Live_serversdisableDVR').val('');
        $('#Live_serversdisableGifThumbs').val('');
        $('#Live_serversuseAadaptiveMode').val('');
        $('#Live_serversprotectLive').val('');
        $('#Live_serversgetRemoteFile').val('');
        $('#Live_serversrestreamerURL').val('');
        $('#Live_serverscontrolURL').val('');
    }
    $(document).ready(function () {
        $('#addLiveBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/addLive_serversVideo.php',
                data: $('#panelLive_serversForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelLive_serversForm").trigger("reset");
                    }
                    clearLive_serversForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Live_serverstableVar = $('#Live_serversTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_servers/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "name"},
                {"data": "rtmp_server"},
                {"data": "status"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Live_serversbtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newLive_servers').on('click', function (e) {
            e.preventDefault();
            $('#panelLive_serversForm').trigger("reset");
            $('#Live_serversid').val('');
        });
        $('#panelLive_serversForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_servers/add.json.php',
                data: $('#panelLive_serversForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        avideoAlert("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                        $("#panelLive_serversForm").trigger("reset");
                    }
                    Live_serverstableVar.ajax.reload();
                    $('#Live_serversid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Live_serversTable').on('click', 'button.delete_Live_servers', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_serverstableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/Live_servers/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Live_serverstableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Live_serversTable').on('click', 'button.edit_Live_servers', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_serverstableVar.row(tr).data();
            $('#Live_serversid').val(data.id);
            $('#Live_serversname').val(data.name);
            $('#Live_serversurl').val(data.url);
            $('#Live_serversstatus').val(data.status);
            $('#Live_serversrtmp_server').val(data.rtmp_server);
            $('#Live_serversplayerServer').val(data.playerServer);
            $('#Live_serversstats_url').val(data.stats_url);
            $('#Live_serversdisableDVR').val(data.disableDVR);
            $('#Live_serversdisableGifThumbs').val(data.disableGifThumbs);
            $('#Live_serversuseAadaptiveMode').val(data.useAadaptiveMode);
            $('#Live_serversprotectLive').val(data.protectLive);
            $('#Live_serversgetRemoteFile').val(data.getRemoteFile);
            $('#Live_serversrestreamerURL').val(data.restreamerURL);
            $('#Live_serverscontrolURL').val(data.controlURL);
        });
        $('#Live_serversTable').on('click', 'button.go_Live_servers', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Live_serverstableVar.row(tr).data();
            document.location = "<?php echo $global['webSiteRootURL']; ?>plugin/Live/?live_servers_id="+data.id;
        });
    });
</script>
