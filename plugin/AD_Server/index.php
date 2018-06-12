<?php
require_once '../../videos/configuration.php';

$plugin = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');

if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not do this"));
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: VAST</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>            
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/> 
        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

        <style>
            #campaignVideosTable td img {
                height: 50px;
                margin: 5px;
            }
            .ui-autocomplete{
                z-index: 9999999;
            }
        </style>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fas fa-link"></i> <?php echo __("Configure your Ads"); ?>
                    <div class="pull-right" style="width: 200px;">
                        <div class="material-switch ">
                            <?php echo __("Enable Ads Plugin"); ?> &nbsp;&nbsp;&nbsp; 
                            <input name="enable1" id="enable1" type="checkbox" value="0" class="pluginSwitch" <?php
                            if (is_object($plugin)) {
                                echo " checked='checked' ";
                            }
                            ?> />
                            <label for="enable1" class="label-success"></label>
                        </div>
                    </div>                    
                </div>
                <div class="panel-body"> 
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="panel panel-default ">
                                <div class="panel-heading"><?php echo __("Create Campaign"); ?></div>
                                <div class="panel-body">
                                    <form id="panelForm">
                                        <div class="row">
                                            <input type="hidden" name="campId" id="campId" value="" >
                                            <div class="form-group col-sm-12">
                                                <label for="name"><?php echo __("Name"); ?>:</label>
                                                <input type="text" id="name" name="name" class="form-control input-sm" placeholder="<?php echo __("Name"); ?>" required="true">
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label for="startDate"><?php echo __("Starts on"); ?>:</label>
                                                <input type="text" id="startDate" name="start_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("Starts on"); ?>" required >
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label for="endDate"><?php echo __("End on"); ?>:</label>
                                                <input type="text" id="endDate" name="end_date" class="form-control datepickerLink input-sm" placeholder="<?php echo __("End on"); ?>" required>
                                            </div>
                                            <div class="form-group col-sm-6">
                                                <label for="maxPrints"><?php echo __("Max Prints"); ?>:</label>
                                                <input type="number" id="maxPrints" name="maxPrints" class="form-control input-sm" placeholder="<?php echo __("End on"); ?>" required>
                                            </div> 
                                            <div class="form-group col-sm-6">
                                                <label for="status"><?php echo __("Status"); ?>:</label>
                                                <select class="form-control input-sm" name="status" id="status">
                                                    <option value="a"><?php echo __("Active"); ?></option>
                                                    <option value="i"><?php echo __("Inactive"); ?></option>
                                                </select>
                                            </div> 
                                            <div class="form-group col-sm-6">
                                                <label for="visibility"><?php echo __("Visibility"); ?>:</label>
                                                <select class="form-control input-sm" name="visibility" id="visibility">
                                                    <option value="listed"><?php echo __("Listed"); ?></option>
                                                    <option value="unlisted"><?php echo __("Unlisted"); ?></option>
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
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <label><?php echo __("VMAP Link"); ?>:</label>
                                    <input type="text" class="form-control input-sm" readonly value="<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/VMAP.php" >
                                </div>
                            </div>
                            <div class="panel panel-default ">
                                <div class="panel-heading"><?php echo __("Edit Campaigns"); ?></div>
                                <div class="panel-body">
                                    <table id="campaignTable" class="display" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Status</th>
                                                <th>Prints Left</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Start</th>
                                                <th>End</th>
                                                <th>Status</th>
                                                <th>Prints Left</th>
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

            <div id="videoFormModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document" style="width: 800px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __("Video Form"); ?></h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: scroll;">
                            <div class="row">
                                <h3><?php echo __("Add Videos into Campaign"); ?> - <strong id="campaignName"></strong></h3>
                                <div class="col-md-4">
                                    <img id="inputVideo-poster" src="<?php echo $global['webSiteRootURL']; ?>img/notfound.jpg" class="ui-state-default img-responsive" alt="">
                                </div>
                                <div class="col-md-8">                                        
                                    <input id="inputVideo" placeholder="<?php echo __("Video"); ?>" class="form-control">
                                    <input id="inputVideoClean" placeholder="<?php echo __("Video URL"); ?>" class="form-control" readonly="readonly">
                                    <div id="adDetails">
                                        <input id="inputVideoURI" type="url" placeholder="<?php echo __("Video Redirect URI"); ?>" class="form-control" >
                                        <input id="inputVideoTitle" placeholder="<?php echo __("Ad Title"); ?>" class="form-control" >
                                    </div>
                                    <input type="hidden" id="vast_campaigns_id">                                        
                                    <input type="hidden" id="videos_id">                                        
                                </div>
                            </div>
                            <hr>
                            <button type="button" class="btn btn-success" id="addVideoBtn"><?php echo __("Add Video"); ?></button>                
                            <hr>
                            <div class="row">
                                <div class="col-md-12"> 
                                    <table id="campaignVideosTable" class="display" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Title</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Title</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->


            <div id="chartFormModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document" style="width: 800px;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"><?php echo __("Charts"); ?></h4>
                        </div>
                        <div class="modal-body" style="max-height: 70vh; overflow-y: scroll;">
                            <canvas id="canvas"></canvas>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <div id="btnModelVideos" style="display: none;">
                <button href="" class="editor_delete_video btn btn-danger btn-xs">
                    <i class="fa fa-trash"></i>
                </button>
            </div>    

            <div id="btnModelLinks" style="display: none;">
                <div class="btn-group pull-right"> 
                    <button href="" class="editor_add_video btn btn-success btn-xs">
                        <i class="fa fa-video"></i> Add Video
                    </button>
                    <button href="" class="editor_chart btn btn-info btn-xs">
                        <i class="fas fa-chart-area "></i>
                    </button>
                    <button href="" class="editor_edit_link btn btn-default btn-xs">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button href="" class="editor_delete_link btn btn-danger btn-xs">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>    
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/js/Chart.bundle.min.js"></script>

        <script type="text/javascript">
            function clearVideoForm() {
                $('#inputVideo-poster').attr('src', "<?php echo $global['webSiteRootURL']; ?>img/notfound.jpg");
                $('#inputVideo').val('');
                $('#inputVideoClean').val('');
                $('#inputVideoURI').val('');
                $('#inputVideoTitle').val('');
                $('#adDetails').slideUp();
                $('#videos_id').val(0);
            }
            var barChartData = {
                labels: ['Impression', 'First Quartile', 'Midpoint', 'Third Quartile', 'Complete', 'ClickThrough'],
                datasets: [{
                        label: 'Campaign',
                        backgroundColor: 'rgba(0, 100, 255, 0.3)',
                        borderColor: 'rgba(0, 100, 255, 0.5)',
                        borderWidth: 1,
                        data: [1, 1, 1, 1, 1, 1]
                    }]

            };
            $(document).ready(function () {
                var ctx = document.getElementById('canvas').getContext('2d');
                window.myBar = new Chart(ctx, {
                    type: 'bar',
                    data: barChartData,
                    options: {
                        scales: {
                            yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function (value, index, values) {
                                            if (Math.floor(value) === value) {
                                                return value;
                                            }
                                        }
                                    }
                                }]
                        },
                        responsive: true,
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Chart.js Bar Chart'
                        }
                    }
                });
                $(".pluginSwitch").on("change", function (e) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
                        data: {"uuid": "3f2a707f-3c06-4b78-90f9-a22f2fda92ef", "name": "AD_Server", "dir": "AD_Server", "enable": $('#enable1').is(":checked")},
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });

                $("#inputVideo").autocomplete({
                    minLength: 0,
                    source: function (req, res) {
                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>videos.json',
                            type: "POST",
                            data: {
                                searchPhrase: req.term
                            },
                            success: function (data) {
                                res(data.rows);
                            }
                        });
                    },
                    focus: function (event, ui) {
                        $("#inputVideo").val(ui.item.title);
                        return false;
                    },
                    select: function (event, ui) {
                        $("#inputVideo").val(ui.item.title);
                        $("#inputVideoClean").val('<?php echo $global['webSiteRootURL']; ?>video/' + ui.item.clean_title);
                        $("#inputVideo-id").val(ui.item.id);
                        $("#inputVideo-poster").attr("src", "<?php echo $global['webSiteRootURL']; ?>videos/" + ui.item.filename + ".jpg");
                        $('#videos_id').val(ui.item.id);
                        $('#adDetails').slideDown();
                        return false;
                    }
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    return $("<li>").append("<div>" + item.title + "<br><?php echo __("Uploaded By"); ?>: " + item.user + "</div>").appendTo(ul);
                };
                var tableVideos = $('#campaignVideosTable').DataTable({
                    "ajax": {
                        "url": "<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/campaignsVideos.json.php",
                        "type": "POST",
                        "data": function (d) {
                            d.id = $('#vast_campaigns_id').val();
                        }
                    },
                    "type": "POST",
                    "columns": [
                        {
                            sortable: false,
                            data: null,
                            "render": function (data, type, full, meta) {
                                return '<img src="' + full.poster.thumbsJpg + '" class="ui-state-default img-responsive" alt="">';
                            }, "width": "20%"
                        },
                        {
                            sortable: true,
                            data: 'title',
                            "render": function (data, type, full, meta) {
                                return full.title + "<div><small>Title: " + full.ad_title + "<br>URL:  " + full.link + "</small></div>";
                            }
                        },
                        {
                            sortable: false,
                            data: null,
                            defaultContent: $('#btnModelVideos').html()
                        }
                    ],
                    select: true,
                });
                $('#campaignVideosTable').on('click', 'button.editor_delete_video', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableVideos.row(tr).data();
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
                                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/deleteCampaignVideo.json.php",
                                    data: data

                                }).done(function (resposta) {
                                    if (resposta.error) {
                                        swal("<?php echo __("Sorry!"); ?>", resposta.msg, "error");
                                    }
                                    tableVideos.ajax.reload();
                                    modal.hidePleaseWait();
                                });
                            });
                });
                $('#addVideoBtn').click(function () {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/addCampaignVideo.php',
                        data: {vast_campaigns_id: $('#vast_campaigns_id').val(), videos_id: $('#videos_id').val(), title: $('#inputVideoURI').val(), uri: $('#inputVideoTitle').val()},
                        type: 'post',
                        success: function (response) {
                            if (response.error) {
                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                            } else {
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                                $("#panelForm").trigger("reset");
                            }
                            clearVideoForm();
                            tableVideos.ajax.reload();
                            modal.hidePleaseWait();
                        }
                    });
                });

                var tableLinks = $('#campaignTable').DataTable({
                    "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/campaigns.json.php",
                    "columns": [
                        {"data": "name"},
                        {"data": "start_date"},
                        {"data": "end_date"},
                        {"data": "status", width: 10},
                        {sortable: false, "data": "printsLeft"},
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
                    $('#panelForm').trigger("reset");
                    $('#campId').val('');
                });

                $('#panelForm').on('submit', function (e) {
                    e.preventDefault();
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/addCampaign.php',
                        data: $('#panelForm').serialize(),
                        type: 'post',
                        success: function (response) {
                            if (response.error) {
                                swal("<?php echo __("Sorry!"); ?>", response.msg, "error");
                            } else {
                                swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your register has been saved!"); ?>", "success");
                                $("#panelForm").trigger("reset");
                            }
                            tableLinks.ajax.reload();
                            $('#campId').val('');
                            modal.hidePleaseWait();
                        }
                    });
                });
                $('#campaignTable').on('click', 'button.editor_add_video', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableLinks.row(tr).data();
                    $('#campaignName').html(data.name);
                    $('#vast_campaigns_id').val(data.id);
                    clearVideoForm();
                    $('#videoFormModal').modal();
                    tableVideos.ajax.reload();
                });
                $('#campaignTable').on('click', 'button.editor_chart', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableLinks.row(tr).data();
                    console.log(data);
                    barChartData.datasets[0].label = '<?php echo __("Campaign"); ?>: '+data.name;
                    //'Impression', 'First Quartile', 'Midpoint', 'Third Quartile', 'Complete', 'ClickThrough'
                    barChartData.datasets[0].data = [
                        data.data.Impression,
                        data.data.firstQuartile,
                        data.data.midpoint,
                        data.data.thirdQuartile,
                        data.data.complete,
                        data.data.ClickThrough
                    ];
                    $('#chartFormModal').modal();
                    window.myBar.update();
                });
                $('#campaignTable').on('click', 'button.editor_delete_link', function (e) {
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
                                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/AD_Server/view/deleteCampaign.json.php",
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

                $('#campaignTable').on('click', 'button.editor_edit_link', function (e) {
                    e.preventDefault();
                    var tr = $(this).closest('tr')[0];
                    var data = tableLinks.row(tr).data();
                    $('#campId').val(data.id);
                    $('#name').val(data.name);
                    $('#startDate').val(data.start_date);
                    $('#endDate').val(data.end_date);
                    $('#maxPrints').val(data.cpm_max_prints);
                    $('#status').val(data.status);
                    $('#visibility').val(data.visibility);
                });
            });
        </script>
    </body>
</html>
