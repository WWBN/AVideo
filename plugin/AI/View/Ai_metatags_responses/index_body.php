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
                        <form id="panelAi_metatags_responsesForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Ai_metatags_responsesid" value="" >
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesvideoTitles"><?php echo __("VideoTitles"); ?>:</label>
                                        <textarea id="Ai_metatags_responsesvideoTitles" name="videoTitles" class="form-control input-sm" placeholder="<?php echo __("VideoTitles"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responseskeywords"><?php echo __("Keywords"); ?>:</label>
                                        <textarea id="Ai_metatags_responseskeywords" name="keywords" class="form-control input-sm" placeholder="<?php echo __("Keywords"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesprofessionalDescription"><?php echo __("ProfessionalDescription"); ?>:</label>
                                        <textarea id="Ai_metatags_responsesprofessionalDescription" name="professionalDescription" class="form-control input-sm" placeholder="<?php echo __("ProfessionalDescription"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsescasualDescription"><?php echo __("CasualDescription"); ?>:</label>
                                        <textarea id="Ai_metatags_responsescasualDescription" name="casualDescription" class="form-control input-sm" placeholder="<?php echo __("CasualDescription"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesshortSummary"><?php echo __("ShortSummary"); ?>:</label>
                                        <textarea id="Ai_metatags_responsesshortSummary" name="shortSummary" class="form-control input-sm" placeholder="<?php echo __("ShortSummary"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesmetaDescription"><?php echo __("MetaDescription"); ?>:</label>
                                        <textarea id="Ai_metatags_responsesmetaDescription" name="metaDescription" class="form-control input-sm" placeholder="<?php echo __("MetaDescription"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesrrating"><?php echo __("Rrating"); ?>:</label>
                                        <input type="text" id="Ai_metatags_responsesrrating" name="rrating" class="form-control input-sm" placeholder="<?php echo __("Rrating"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesrratingJustification"><?php echo __("RratingJustification"); ?>:</label>
                                        <textarea id="Ai_metatags_responsesrratingJustification" name="rratingJustification" class="form-control input-sm" placeholder="<?php echo __("RratingJustification"); ?>" required="true"></textarea>
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesprompt_tokens"><?php echo __("Prompt Tokens"); ?>:</label>
                                        <input type="number" step="1" id="Ai_metatags_responsesprompt_tokens" name="prompt_tokens" class="form-control input-sm" placeholder="<?php echo __("Prompt Tokens"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsescompletion_tokens"><?php echo __("completion Tokens"); ?>:</label>
                                        <input type="number" step="1" id="Ai_metatags_responsescompletion_tokens" name="completion_tokens" class="form-control input-sm" placeholder="<?php echo __("completion Tokens"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesprice_prompt_tokens"><?php echo __("Price Prompt Tokens"); ?>:</label>
                                        <input type="text" id="Ai_metatags_responsesprice_prompt_tokens" name="price_prompt_tokens" class="form-control input-sm" placeholder="<?php echo __("Price Prompt Tokens"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                        <label for="Ai_metatags_responsesprice_completion_tokens"><?php echo __("Price Completion Tokens"); ?>:</label>
                                        <input type="text" id="Ai_metatags_responsesprice_completion_tokens" name="price_completion_tokens" class="form-control input-sm" placeholder="<?php echo __("Price Completion Tokens"); ?>" required="true">
                                    </div>
<div class="form-group col-sm-12">
                                    <label for="Ai_metatags_responsesai_responses_id"><?php echo __("Ai Responses Id"); ?>:</label>
                                    <select class="form-control input-sm" name="ai_responses_id" id="Ai_metatags_responsesai_responses_id">
                                        <?php
                                        $options = Ai_metatags_responses::getAllAi_responses();
                                        foreach ($options as $value) {
                                            echo '<option value="'.$value['id'].'">'.$value['id'].'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newAi_metatags_responsesLink" onclick="clearAi_metatags_responsesForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Ai_metatags_responsesTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
<th><?php echo __("VideoTitles"); ?></th>
<th><?php echo __("Keywords"); ?></th>
<th><?php echo __("ProfessionalDescription"); ?></th>
<th><?php echo __("CasualDescription"); ?></th>
<th><?php echo __("ShortSummary"); ?></th>
<th><?php echo __("MetaDescription"); ?></th>
<th><?php echo __("Rrating"); ?></th>
<th><?php echo __("RratingJustification"); ?></th>
<th><?php echo __("Prompt Tokens"); ?></th>
<th><?php echo __("completion Tokens"); ?></th>
<th><?php echo __("Price Prompt Tokens"); ?></th>
<th><?php echo __("Price Completion Tokens"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
<th><?php echo __("VideoTitles"); ?></th>
<th><?php echo __("Keywords"); ?></th>
<th><?php echo __("ProfessionalDescription"); ?></th>
<th><?php echo __("CasualDescription"); ?></th>
<th><?php echo __("ShortSummary"); ?></th>
<th><?php echo __("MetaDescription"); ?></th>
<th><?php echo __("Rrating"); ?></th>
<th><?php echo __("RratingJustification"); ?></th>
<th><?php echo __("Prompt Tokens"); ?></th>
<th><?php echo __("completion Tokens"); ?></th>
<th><?php echo __("Price Prompt Tokens"); ?></th>
<th><?php echo __("Price Completion Tokens"); ?></th>
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
<div id="Ai_metatags_responsesbtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Ai_metatags_responses btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Ai_metatags_responses btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearAi_metatags_responsesForm() {
    $('#Ai_metatags_responsesid').val('');
$('#Ai_metatags_responsesvideoTitles').val('');
$('#Ai_metatags_responseskeywords').val('');
$('#Ai_metatags_responsesprofessionalDescription').val('');
$('#Ai_metatags_responsescasualDescription').val('');
$('#Ai_metatags_responsesshortSummary').val('');
$('#Ai_metatags_responsesmetaDescription').val('');
$('#Ai_metatags_responsesrrating').val('');
$('#Ai_metatags_responsesrratingJustification').val('');
$('#Ai_metatags_responsesprompt_tokens').val('');
$('#Ai_metatags_responsescompletion_tokens').val('');
$('#Ai_metatags_responsesprice_prompt_tokens').val('');
$('#Ai_metatags_responsesprice_completion_tokens').val('');
$('#Ai_metatags_responsesai_responses_id').val('');
    }
    $(document).ready(function () {
    $('#addAi_metatags_responsesBtn').click(function () {
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/addAi_metatags_responsesVideo.php',
            data: $('#panelAi_metatags_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_metatags_responsesForm").trigger("reset");
                }
                clearAi_metatags_responsesForm();
                tableVideos.ajax.reload();
                modal.hidePleaseWait();
            }
        });
    });
    var Ai_metatags_responsestableVar = $('#Ai_metatags_responsesTable').DataTable({
        serverSide: true,
        "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_metatags_responses/list.json.php",
        "columns": [
        {"data": "id"},
{"data": "videoTitles"},
{"data": "keywords"},
{"data": "professionalDescription"},
{"data": "casualDescription"},
{"data": "shortSummary"},
{"data": "metaDescription"},
{"data": "rrating"},
{"data": "rratingJustification"},
{"data": "prompt_tokens"},
{"data": "completion_tokens"},
{"data": "price_prompt_tokens"},
{"data": "price_completion_tokens"},
        {
        sortable: false,
                data: null,
                defaultContent: $('#Ai_metatags_responsesbtnModelLinks').html()
        }
        ],
        select: true,
    });
    $('#newAi_metatags_responses').on('click', function (e) {
    e.preventDefault();
    $('#panelAi_metatags_responsesForm').trigger("reset");
    $('#Ai_metatags_responsesid').val('');
    });
    $('#panelAi_metatags_responsesForm').on('submit', function (e) {
        e.preventDefault();
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL+'plugin/AI/View/Ai_metatags_responses/add.json.php',
            data: $('#panelAi_metatags_responsesForm').serialize(),
            type: 'post',
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToast("<?php echo __("Your register has been saved!"); ?>");
                    $("#panelAi_metatags_responsesForm").trigger("reset");
                }
                Ai_metatags_responsestableVar.ajax.reload();
                $('#Ai_metatags_responsesid').val('');
                modal.hidePleaseWait();
            }
        });
    });
    $('#Ai_metatags_responsesTable').on('click', 'button.delete_Ai_metatags_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_metatags_responsestableVar.row(tr).data();
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
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/AI/View/Ai_metatags_responses/delete.json.php",
                    data: data

            }).done(function (resposta) {
            if (resposta.error) {
                avideoAlertError(resposta.msg);
            }
            Ai_metatags_responsestableVar.ajax.reload();
            modal.hidePleaseWait();
            });
            } else {

            }
            });
    });
    $('#Ai_metatags_responsesTable').on('click', 'button.edit_Ai_metatags_responses', function (e) {
    e.preventDefault();
    var tr = $(this).closest('tr')[0];
    var data = Ai_metatags_responsestableVar.row(tr).data();
    $('#Ai_metatags_responsesid').val(data.id);
$('#Ai_metatags_responsesvideoTitles').val(data.videoTitles);
$('#Ai_metatags_responseskeywords').val(data.keywords);
$('#Ai_metatags_responsesprofessionalDescription').val(data.professionalDescription);
$('#Ai_metatags_responsescasualDescription').val(data.casualDescription);
$('#Ai_metatags_responsesshortSummary').val(data.shortSummary);
$('#Ai_metatags_responsesmetaDescription').val(data.metaDescription);
$('#Ai_metatags_responsesrrating').val(data.rrating);
$('#Ai_metatags_responsesrratingJustification').val(data.rratingJustification);
$('#Ai_metatags_responsesprompt_tokens').val(data.prompt_tokens);
$('#Ai_metatags_responsescompletion_tokens').val(data.completion_tokens);
$('#Ai_metatags_responsesprice_prompt_tokens').val(data.price_prompt_tokens);
$('#Ai_metatags_responsesprice_completion_tokens').val(data.price_completion_tokens);
$('#Ai_metatags_responsesai_responses_id').val(data.ai_responses_id);
    });
    });
</script>
