<link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/> 
<link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-link"></i> <?php echo __("Add an external Live Link"); ?></div>
    <div class="panel-body"> 
        <div class="row">
            <div class="col-sm-4">
                <form id="liveLinksForm">
                    <div class="row">
                        <input type="hidden" name="linkId" id="linkId" value="" >
                        <div class="form-group col-sm-12">
                            <label for="linkTitle"><?php echo __("Title"); ?>:</label>
                            <input type="text" id="linkTitle" name="title" class="form-control input-sm" placeholder="<?php echo __("Title"); ?>" required="true">
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="linkLink"><?php echo __("Link"); ?> (m3u8):</label>
                            <input type="text" id="linkLink" name="link" class="form-control input-sm" placeholder="HLS .m3u8 Link" required="true">
                        </div>
                        <div class="form-group col-sm-12">
                            <label for="linkDescription"><?php echo __("Description"); ?>:</label>
                            <textarea id="linkDescription" name="description" class="form-control input-sm" placeholder="<?php echo __("Description"); ?>" required="true"></textarea>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="inputLinkStarts"><?php echo __("Starts on"); ?>:</label>
                            <input type="text" id="inputLinkStarts" name="start_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("Starts on"); ?>" required >
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="inputLinkEnd"><?php echo __("End on"); ?>:</label>
                            <input type="text" id="inputLinkEnd" name="end_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("End on"); ?>" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="linkType"><?php echo __("Type"); ?>:</label>
                            <select class="form-control input-sm" name="type" id="linkType">
                                <option value="public"><?php echo __("Public"); ?></option>
                                <option value="unlisted"><?php echo __("Unlisted"); ?></option>
                                <option value="logged_only"><?php echo __("Logged Users Only"); ?></option>
                            </select>
                        </div> 
                        <div class="form-group col-sm-6">
                            <label for="linkStatus"><?php echo __("Status"); ?>:</label>
                            <select class="form-control input-sm" name="status" id="linkStatus">
                                <option value="a"><?php echo __("Active"); ?></option>
                                <option value="i"><?php echo __("Inactive"); ?></option>
                            </select>
                        </div> 
                        <div class="form-group col-sm-12">
                            <div class="btn-group pull-right">
                                <span class="btn btn-success" id="newLiveLink"><i class="fas fa-plus"></i> <?php echo __("New"); ?></span>
                                <button class="btn btn-primary" id="addLiveLink" type="submit"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
                            </div> 
                        </div> 
                    </div>
                </form>
            </div>
            <div class="col-sm-8">
                <div class="panel panel-default ">
                    <div class="panel-heading"><?php echo __("Live Events"); ?></div>
                    <div class="panel-body">
                        <table id="exampleLinks" class="display" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Title</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Type</th>
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
<div id="btnModelLinks" style="display: none;">
    <div class="btn-group pull-right"> 
        <button href="" class="editor_edit_link btn btn-default btn-xs">
            <i class="fa fa-edit"></i>
        </button>
        <button href="" class="editor_delete_link btn btn-danger btn-xs">
            <i class="fa fa-trash"></i>
        </button>
    </div>    
</div>
<script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
<script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function () {

        var tableLinks = $('#exampleLinks').DataTable({
            "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/LiveLinks/view/liveLinks.json.php",
            "columns": [
                {"data": "title"},
                {"data": "start_date"},
                {"data": "end_date"},
                {"data": "status", width:10},
                {"data": "type"},
                {
                    sortable: false,
                    data: null,
                    defaultContent: $('#btnModelLinks').html()
                }
            ],
            select: true,
        });
        $('.datepickerLink').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            autoclose: true
        });

        $('#newLiveLink').on('click', function (e) {
            e.preventDefault();
            $('#liveLinksForm').trigger("reset");
            $('#linkId').val('');
        });

        $('#liveLinksForm').on('submit', function (e) {
            e.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>plugin/LiveLinks/view/addLiveLink.php',
                data: $('#liveLinksForm').serialize(),
                type: 'post',
                success: function (response) {
                    if (response.error) {
                        swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                    } else {
                        swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your link has been saved!"); ?>", "success");
                        $("#liveLinksForm").trigger("reset");
                    }
                    tableLinks.ajax.reload();
                    $('#linkId').val('');
                    modal.hidePleaseWait();
                }
            });
        });
        $('#exampleLinks').on('click', 'button.editor_delete_link', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();
            swal({
                title: "<?php echo __("Are you sure?"); ?>",
                text: "<?php echo __("You will not be able to recover this action!"); ?>",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "<?php echo __("Yes, delete it!"); ?>",
                closeOnConfirm: true
            },
                    function () {
                        modal.showPleaseWait();
                        $.ajax({
                            type: "POST",
                            url: "<?php echo $global['webSiteRootURL']; ?>plugin/LiveLinks/view/delete_liveLink.json.php",
                            data: data

                        }).done(function (resposta) {
                            if (resposta.error) {
                                swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                            }
                            tableLinks.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    });
        });

        $('#exampleLinks').on('click', 'button.editor_edit_link', function (e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();
            $('#linkId').val(data.id);
            $('#linkTitle').val(data.title);
            $('#linkDescription').val(data.description);
            $('#linkLink').val(data.link);
            $('#inputLinkStarts').val(data.start_date);
            $('#inputLinkEnd').val(data.end_date);
            $('#linkType').val(data.type);
            $('#linkStatus').val(data.status);
        });
    });
</script>