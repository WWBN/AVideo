<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../../videos/configuration.php';
}
if (!User::isAdmin()) {
    forbiddenPage("Must be admin");
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
                        <form id="panelUsers_extra_infoForm">
                            <div class="row">
                                <input type="hidden" name="id" id="Users_extra_infoid" value="" >
                                <div class="form-group col-sm-12">
                                    <label for="Users_extra_infofield_name"><?php echo __("Field Name"); ?>:</label>
                                    <input type="text" id="Users_extra_infofield_name" name="field_name" class="form-control input-sm" placeholder="<?php echo __("Field Name"); ?>" required="true">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="order"><?php echo __("order"); ?>:</label>
                                    <select class="form-control input-sm" name="order" id="Users_extra_infoorder">
                                        <?php
                                        for($i=0;$i<20;$i++){
                                            echo '<option value="'.$i.'">'.$i.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Users_extra_infofield_type"><?php echo __("Field Type"); ?>:</label>
                                    
                                    <select class="form-control input-sm" name="field_type" id="Users_extra_infofield_type"  required="true">
                                        <?php
                                        $types = Users_extra_info::getTypesOptionArray();
                                        foreach ($types as $key => $value) {
                                            echo '<option value="'.$key.'">'.__($value).'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12" style="display: none;">
                                    <label for="Users_extra_infofield_options"><?php echo __("Field Options"); ?> (<?php echo __("One per line"); ?>):</label>
                                    <textarea id="Users_extra_infofield_options" name="field_options" class="form-control input-sm" placeholder="<?php echo __("Field Options"); ?>" rows="6"></textarea>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="Users_extra_infofield_default_value"><?php echo __("Field Default Value"); ?>:</label>
                                    <input type="text" id="Users_extra_infofield_default_value" name="field_default_value" class="form-control input-sm" placeholder="<?php echo __("Field Default Value"); ?>">
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select class="form-control input-sm" name="status" id="Users_extra_infostatus">
                                        <option value="a"><?php echo __("Active"); ?></option>
                                        <option value="i"><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <div class="form-group col-sm-12" style="display: none;">
                                    <label for="Users_extra_infoparameters"><?php echo __("Parameters"); ?>:</label>
                                    <textarea id="Users_extra_infoparameters" name="parameters" class="form-control input-sm" placeholder="<?php echo __("Parameters"); ?>"></textarea>
                                </div>
                                <div class="form-group col-sm-12">
                                    <div class="btn-group pull-right">
                                        <span class="btn btn-success" id="newUsers_extra_infoLink" onclick="clearUsers_extra_infoForm()"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
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
                        <table id="Users_extra_infoTable" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Field Name"); ?></th>
                                    <th><?php echo __("Field Type"); ?></th>
                                    <th><?php echo __("Field Options"); ?></th>
                                    <th><?php echo __("Field Default Value"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Order"); ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __("Field Name"); ?></th>
                                    <th><?php echo __("Field Type"); ?></th>
                                    <th><?php echo __("Field Options"); ?></th>
                                    <th><?php echo __("Field Default Value"); ?></th>
                                    <th><?php echo __("Status"); ?></th>
                                    <th><?php echo __("Order"); ?></th>
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
<div id="Users_extra_infobtnModelLinks" style="display: none;">
    <div class="btn-group pull-right">
        <button href="" class="edit_Users_extra_info btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="delete_Users_extra_info btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>

<script type="text/javascript">
    function clearUsers_extra_infoForm() {
        $('#Users_extra_infoid').val('');
        $('#Users_extra_infofield_name').val('');
        $('#Users_extra_infofield_type').val('');
        $('#Users_extra_infofield_type').trigger('change');
        $('#Users_extra_infofield_options').val('');
        $('#Users_extra_infofield_default_value').val('');
        $('#Users_extra_infoparameters').val('');
        $('#Users_extra_infostatus').val('');
        $('#Users_extra_infoorder').val(0);
    }
    $(document).ready(function () {
        $('#Users_extra_infofield_type').change(function(){
            if($(this).val() == 'select'){
                $("#Users_extra_infofield_options").parent().slideDown();
            }else{
                $("#Users_extra_infofield_options").parent().slideUp();
            }
        });
        $('#addUsers_extra_infoBtn').click(function () {
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/addUsers_extra_infoVideo.php',
                data: $('#panelUsers_extra_infoForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_extra_infoForm").trigger("reset");
                    }
                    clearUsers_extra_infoForm();
                    tableVideos.ajax.reload();
                    modal.hidePleaseWait();
                }
            });
        });
        var Users_extra_infotableVar = $('#Users_extra_infoTable').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_extra_info/list.json.php",
            "columns": [
                {"data": "id"},
                {"data": "field_name"},
                {"data": "field_type"},
                {"data": "field_options"},
                {"data": "field_default_value"},
                {"data": "status"},
                {"data": "order"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#Users_extra_infobtnModelLinks').html()
                }
            ],
            select: true,
        });
        $('#newUsers_extra_info').on('click', function (e) {
            e.preventDefault();
            $('#panelUsers_extra_infoForm').trigger("reset");
            $('#Users_extra_infoid').val('');
        });
        $('#panelUsers_extra_infoForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_extra_info/add.json.php',
                data: $('#panelUsers_extra_infoForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        avideoToast("<?php echo __("Your register has been saved!"); ?>");
                        $("#panelUsers_extra_infoForm").trigger("reset");
                    }
                    Users_extra_infotableVar.ajax.reload();
                    $('#Users_extra_infoid').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#Users_extra_infoTable').on('click', 'button.delete_Users_extra_info', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_extra_infotableVar.row(tr).data();
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
                                url: "<?php echo $global['webSiteRootURL']; ?>plugin/CustomizeUser/View/Users_extra_info/delete.json.php",
                                data: data

                            }).done(function (resposta) {
                                if (resposta.error) {
                                    swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                }
                                Users_extra_infotableVar.ajax.reload();
                                modal.hidePleaseWait();
                            });
                        } else {

                        }
                    });
        });
        $('#Users_extra_infoTable').on('click', 'button.edit_Users_extra_info', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = Users_extra_infotableVar.row(tr).data();
            $('#Users_extra_infoid').val(data.id);
            $('#Users_extra_infofield_name').val(data.field_name);
            $('#Users_extra_infofield_type').val(data.field_type);
            $('#Users_extra_infofield_type').trigger('change');
            $('#Users_extra_infofield_options').val(data.field_options);
            $('#Users_extra_infofield_default_value').val(data.field_default_value);
            $('#Users_extra_infoparameters').val(data.parameters);
            $('#Users_extra_infostatus').val(data.status);
            $('#Users_extra_infoorder').val(data.order);
        });
    });
</script>
