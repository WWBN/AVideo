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
                        <form id="panelAi_transcribe_responsesForm">
                            <div class="row">
                                <div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsesai"><?php echo __("Ai"); ?>:</label>
                                        <input type="number" step="1" id="Ai_transcribe_responsesai" name="ai" class="form-control input-sm" placeholder="<?php echo __("Ai"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsesvtt"><?php echo __("Vtt"); ?>:</label>
                                        <textarea id="Ai_transcribe_responsesvtt" name="vtt" class="form-control input-sm" placeholder="<?php echo __("Vtt"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responseslanguage"><?php echo __("Language"); ?>:</label>
                                        <input type="text" id="Ai_transcribe_responseslanguage" name="language" class="form-control input-sm" placeholder="<?php echo __("Language"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsesduration"><?php echo __("Duration"); ?>:</label>
                                        <input type="text" id="Ai_transcribe_responsesduration" name="duration" class="form-control input-sm" placeholder="<?php echo __("Duration"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsestext"><?php echo __("Text"); ?>:</label>
                                        <textarea id="Ai_transcribe_responsestext" name="text" class="form-control input-sm" placeholder="<?php echo __("Text"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsestotal_price"><?php echo __("Total Price"); ?>:</label>
                                        <input type="text" id="Ai_transcribe_responsestotal_price" name="total_price" class="form-control input-sm" placeholder="<?php echo __("Total Price"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsessize_in_bytes"><?php echo __("Size In Bytes"); ?>:</label>
                                        <input type="number" step="1" id="Ai_transcribe_responsessize_in_bytes" name="size_in_bytes" class="form-control input-sm" placeholder="<?php echo __("Size In Bytes"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_transcribe_responsesmp3_url"><?php echo __("Mp3 Url"); ?>:</label>
                                        <input type="text" id="Ai_transcribe_responsesmp3_url" name="mp3_url" class="form-control input-sm" placeholder="<?php echo __("Mp3 Url"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                    <label for="Ai_transcribe_responsesai_responses_id"><?php echo __("Ai Responses Id"); ?>:</label>
                                    <select class="form-control input-sm" name="ai_responses_id" id="Ai_transcribe_responsesai_responses_id">
                                        <?php
                                        $options = Ai_transcribe_responses::getAllAi_responses();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newAi_transcribe_responsesLink" onclick="clearAi_transcribe_responsesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Ai_transcribe_responsesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><?php echo __("Ai"); ?></th>
<th><?php echo __("Vtt"); ?></th>
<th><?php echo __("Language"); ?></th>
<th><?php echo __("Duration"); ?></th>
<th><?php echo __("Text"); ?></th>
<th><?php echo __("Total Price"); ?></th>
<th><?php echo __("Size In Bytes"); ?></th>
<th><?php echo __("Mp3 Url"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th><?php echo __("Ai"); ?></th>
<th><?php echo __("Vtt"); ?></th>
<th><?php echo __("Language"); ?></th>
<th><?php echo __("Duration"); ?></th>
<th><?php echo __("Text"); ?></th>
<th><?php echo __("Total Price"); ?></th>
<th><?php echo __("Size In Bytes"); ?></th>
<th><?php echo __("Mp3 Url"); ?></th>
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
<div id="Ai_transcribe_responsesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Ai_transcribe_responses btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Ai_transcribe_responses btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearAi_transcribe_responsesForm() {
    $('#Ai_transcribe_responsesai').val('');
$('#Ai_transcribe_responsesvtt').val('');
$('#Ai_transcribe_responseslanguage').val('');
$('#Ai_transcribe_responsesduration').val('');
$('#Ai_transcribe_responsestext').val('');
$('#Ai_transcribe_responsestotal_price').val('');
$('#Ai_transcribe_responsessize_in_bytes').val('');
$('#Ai_transcribe_responsesmp3_url').val('');
$('#Ai_transcribe_responsesai_responses_id').val('');
    }
    $(document).ready(function () {
    $('#addAi_transcribe_responsesBtn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/addAi_transcribe_responsesVideo.php',
            data: $('#panelAi_transcribe_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_transcribe_responsesForm").trigger("reset");
                }
                clearAi_transcribe_responsesForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Ai_transcribe_responsestableVar = $('#Ai_transcribe_responsesTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_transcribe_responses/list.json.php",
        "columns": [
        {"data": "ai"},
{"data": "vtt"},
{"data": "language"},
{"data": "duration"},
{"data": "text"},
{"data": "total_price"},
{"data": "size_in_bytes"},
{"data": "mp3_url"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#Ai_transcribe_responsesbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newAi_transcribe_responses').on('click', function (e) {
    e.preventDefault();
    $('#panelAi_transcribe_responsesForm').trigger("reset");
    $('#Ai_transcribe_responsesid').val('');
    });
    $('#panelAi_transcribe_responsesForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/Ai_transcribe_responses/add.json.php',
            data: $('#panelAi_transcribe_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_transcribe_responsesForm").trigger("reset");
                }
                Ai_transcribe_responsestableVar.ajax.reload();
                $('#Ai_transcribe_responsesid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Ai_transcribe_responsesTable').on('click', 'button.delete_Ai_transcribe_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_transcribe_responsestableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_transcribe_responses/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Ai_transcribe_responsestableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Ai_transcribe_responsesTable').on('click', 'button.edit_Ai_transcribe_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_transcribe_responsestableVar.row(tr).data();
    $('#Ai_transcribe_responsesai').val(data.ai);
$('#Ai_transcribe_responsesvtt').val(data.vtt);
$('#Ai_transcribe_responseslanguage').val(data.language);
$('#Ai_transcribe_responsesduration').val(data.duration);
$('#Ai_transcribe_responsestext').val(data.text);
$('#Ai_transcribe_responsestotal_price').val(data.total_price);
$('#Ai_transcribe_responsessize_in_bytes').val(data.size_in_bytes);
$('#Ai_transcribe_responsesmp3_url').val(data.mp3_url);
$('#Ai_transcribe_responsesai_responses_id').val(data.ai_responses_id);
    });
    });
</script>
