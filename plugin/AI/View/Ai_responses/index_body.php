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
                        <form id="panelAi_responsesForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Ai_responsesid" value="" >
<div class="form-group col-sm-12">
                                        <label for="Ai_responseselapsedTime"><?php echo __("ElapsedTime"); ?>:</label>
                                        <input type="text" id="Ai_responseselapsedTime" name="elapsedTime" class="form-control input-sm" placeholder="<?php echo __("ElapsedTime"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                    <label for="Ai_responsesvideos_id"><?php echo __("Videos Id"); ?>:</label>
                                    <select class="form-control input-sm" name="videos_id" id="Ai_responsesvideos_id">
                                        <?php
                                        $options = Ai_responses::getAllVideos();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newAi_responsesLink" onclick="clearAi_responsesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Ai_responsesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
<th><?php echo __("ElapsedTime"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
<th><?php echo __("ElapsedTime"); ?></th>
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
<div id="Ai_responsesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Ai_responses btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Ai_responses btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearAi_responsesForm() {
    $('#Ai_responsesid').val('');
$('#Ai_responseselapsedTime').val('');
$('#Ai_responsesvideos_id').val('');
    }
    $(document).ready(function () {
    $('#addAi_responsesBtn').click(function () {
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/addAi_responsesVideo.php',
            data: $('#panelAi_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_responsesForm").trigger("reset");
                }
                clearAi_responsesForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Ai_responsestableVar = $('#Ai_responsesTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_responses/list.json.php",
        "columns": [
        {"data": "id"},
{"data": "elapsedTime"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#Ai_responsesbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newAi_responses').on('click', function (e) {
    e.preventDefault();
    $('#panelAi_responsesForm').trigger("reset");
    $('#Ai_responsesid').val('');
    });
    $('#panelAi_responsesForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/Ai_responses/add.json.php',
            data: $('#panelAi_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_responsesForm").trigger("reset");
                }
                Ai_responsestableVar.ajax.reload();
                $('#Ai_responsesid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Ai_responsesTable').on('click', 'button.delete_Ai_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_responsestableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_responses/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Ai_responsestableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Ai_responsesTable').on('click', 'button.edit_Ai_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_responsestableVar.row(tr).data();
    $('#Ai_responsesid').val(data.id);
$('#Ai_responseselapsedTime').val(data.elapsedTime);
$('#Ai_responsesvideos_id').val(data.videos_id);
    });
    });
</script>
