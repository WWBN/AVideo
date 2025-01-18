<?php
$obj = AVideoPlugin::getDataObject("LiveLinks");
?>
<link rel="stylesheet" type="text/css" href="<?php echo getURL('view/css/DataTables/datatables.min.css'); ?>" />
<link href="<?php echo getURL('js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'); ?>" rel="stylesheet" type="text/css" />
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fas fa-link"></i> <?php echo __("Add an external Live Link"); ?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-7">
                <?php
                $width = 1280;
                $height = 720;
                $croppie1 = getCroppie(__("Upload Poster"), "submitLiveLinks", $width, $height, 600);
                echo $croppie1['html'];
                ?>
            </div>
            <div class="col-sm-5">
                <form id="liveLinksForm">
                    <div class="tabbable-line">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a data-toggle="tab" href="#tabStreamMetaData"><i class="fas fa-key"></i> <?php echo __("Meta Data"); ?></a>
                            </li>
                            <li class="">
                                <a data-toggle="tab" href="#tabUserGroups"><i class="fas fa-users"></i> <?php echo __("User Groups"); ?></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="tabStreamMetaData" class="tab-pane fade in active">
                                <div class="row">
                                    <input type="hidden" name="linkId" id="linkId" value="">
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
                                        <input type="text" id="inputLinkStarts" name="start_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("Starts on"); ?>" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="inputLinkEnd"><?php echo __("End on"); ?>:</label>
                                        <input type="text" id="inputLinkEnd" name="end_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("End on"); ?>" required>
                                    </div>
                                    <?php

                                    if (!empty($obj->hideIsRebroadcastOption)) {
                                    ?>
                                        <input id="isRebroadcast" name="isRebroadcast" type="hidden" value="0" />
                                    <?php
                                    } else {
                                    ?>
                                        <div class="form-group col-sm-12" id="publiclyListed">
                                            <i class="fas fa-retweet"></i> <?php echo __("Mark this stream as a Rebroadcast"); ?>
                                            <div class="material-switch pull-right">
                                                <input id="isRebroadcast" name="isRebroadcast" type="checkbox" value="1" <?php echo !empty($trasnmition['isRebroadcast']) ? "checked" : ""; ?> />
                                                <label for="isRebroadcast" class="label-success"></label>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="form-group col-sm-12">
                                        <label for="title"><?php echo __("Category"); ?>:</label>
                                        <?php
                                        echo Layout::getCategorySelect('categories_id');
                                        ?>
                                    </div>
                                    <?php
                                    if (User::isAdmin()) {
                                    ?>
                                        <div class="form-group col-sm-12">
                                            <label for="title"><?php echo __("User"); ?>:</label>
                                            <?php
                                            $updateUserAutocomplete = Layout::getUserAutocomplete(User::getId(), 'users_id');
                                            ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
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
                                </div>
                            </div>
                            <div id="tabUserGroups" class="tab-pane fade">
                                <div class="panel panel-default">
                                    <div class="panel-heading"><?php echo __("Groups That Can See This Stream"); ?><br><small><?php echo __("Uncheck all to make it public"); ?></small></div>
                                    <div class="panel-body" style="max-height: 450px; overflow-y: auto;">
                                        <?php
                                        $ug = UserGroups::getAllUsersGroups();
                                        foreach ($ug as $value) {
                                        ?>
                                            <div class="form-group">
                                                <span class="fa fa-users"></span> <?php echo $value['group_name']; ?>
                                                <div class="material-switch pull-right">
                                                    <input id="group<?php echo $value['id']; ?>" name="userGroups[]" type="checkbox" value="<?php echo $value['id']; ?>" class="userGroups" />
                                                    <label for="group<?php echo $value['id']; ?>" class="label-success"></label>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="panel-footer">
        <div class="btn-group btn-group-justified">
            <button class="btn btn-success" id="newLiveLink" type="button"><i class="fas fa-plus"></i> <?php echo __("New"); ?></button>
            <button class="btn btn-primary" id="addLiveLink" type="button" onclick="<?php echo $croppie1['getCroppieFunction']; ?>"><i class="fas fa-save"></i> <?php echo __("Save"); ?></button>
        </div>
    </div>
</div>
<div class="panel panel-default ">
    <div class="panel-heading"><?php echo __("Live Events"); ?></div>
    <div class="panel-body">
        <table id="exampleLinks" class="display table table-bordered table-responsive table-striped table-hover table-condensed" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>Owner</th>
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
                    <th>Owner</th>
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
<script type="text/javascript" src="<?php echo getURL('view/css/DataTables/datatables.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo getURL('js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'); ?>"></script>
<script type="text/javascript">
    var tableLinks;
    $(document).ready(function() {
        <?php
        echo $croppie1['createCroppie'] . "(webSiteRootURL+'plugin/Live/view/OnAir.jpg');";
        ?>
        tableLinks = $('#exampleLinks').DataTable({
            "ajax": webSiteRootURL + "plugin/LiveLinks/view/liveLinks.json.php",
            "columns": [{
                    "data": "users_id",
                    "render": function(data, type, row) {
                        return row.identification;
                    }
                }, {
                    "data": "title"
                },
                {
                    "data": "start_date"
                },
                {
                    "data": "end_date"
                },
                {
                    "data": "status",
                    width: 10
                },
                {
                    "data": "type"
                },
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

        $('#newLiveLink').on('click', function(e) {
            e.preventDefault();
            $('#liveLinksForm').trigger("reset");
            $('#linkId').val('');
            $('select[name="categories_id"]').val('');
            $('select[name="categories_id"]').trigger('change');
            $("#isRebroadcast").prop("checked", false);
            var imageURL = webSiteRootURL + 'plugin/Live/view/OnAir.jpg';
            <?php
            echo $croppie1["restartCroppie"] . "(imageURL)";
            ?>
        });

        $('#liveLinksForm').on('submit', function(e) {
            e.preventDefault();
        });
        $('#exampleLinks').on('click', 'button.editor_delete_link', function(e) {
            e.preventDefault();
            var tr = $(this).closest('tr')[0];
            var data = tableLinks.row(tr).data();

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
                            url: webSiteRootURL + "plugin/LiveLinks/view/delete_liveLink.json.php",
                            data: data

                        }).done(function(resposta) {
                            if (resposta.error) {
                                avideoAlert("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                            }
                            tableLinks.ajax.reload();
                            modal.hidePleaseWait();
                        });
                    }
                });

        });

        $('#exampleLinks').on('click', 'button.editor_edit_link', function(e) {
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
            $('select[name="categories_id"]').val(data.categories_id);
            $('select[name="categories_id"]').trigger('change');
            $('#linkStatus').val(data.status);
            $(".userGroups").prop("checked", false);
            $("#isRebroadcast").prop("checked", !empty(data.isRebroadcast));
            $('#users_id').val(data.users_id);
            <?php
            if (!empty($updateUserAutocomplete)) {
                echo $updateUserAutocomplete;
            }
            ?>
            for (const index in data.user_groups) {
                $("#group" + data.user_groups[index].id).prop("checked", true);
            }
            var imageURL = data.image.showURL;
            <?php
            echo $croppie1["restartCroppie"] . "(imageURL)";
            ?>
        });
    });

    function submitLiveLinks(image) {
        modal.showPleaseWait();
        var formDataArray = $('#liveLinksForm').serializeArray();
        var data = {};

        // Convert form data to JSON object
        $.each(formDataArray, function(_, field) {
            data[field.name] = field.value;
        });

        // Add the image data to the JSON object
        data.image = image;

        // Send the data as JSON in the AJAX request
        $.ajax({
            url: webSiteRootURL + 'plugin/LiveLinks/view/addLiveLink.php',
            data: data, // Convert to JSON string
            type: 'post',
            success: function(response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                } else {
                    avideoToastSuccess(__("Your link has been saved!"));
                    $("#liveLinksForm").trigger("reset");
                    <?php
                    if (!empty($updateUserAutocomplete)) {
                        echo $updateUserAutocomplete;
                    }
                    ?>
                }
                tableLinks.ajax.reload();
                $('#linkId').val('');
                modal.hidePleaseWait();
            }
        });
    }
</script>