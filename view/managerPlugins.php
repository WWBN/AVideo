<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugins"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/plugin.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: <?php echo __("Plugins"); ?></title>

        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/bootstrap-fileinput/js/fileinput.min.js" type="text/javascript"></script>        
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/bootstrap-fileinput/css/fileinput.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/bootstrap-fileinput/themes/fa/theme.min.js" type="text/javascript"></script>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/bootstrap-fileinput/themes/explorer/theme.min.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>view/bootstrap/bootstrap-fileinput/themes/explorer/theme.min.js" type="text/javascript"></script>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container-fluid">
            <div class="col-md-9">
                <div class="btn-group" >
                    <button type="button" class="btn btn-default" id="upload">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo __("Upload a Plugin"); ?>
                    </button>
                </div>
                <table id="grid" class="table table-condensed table-hover table-striped">
                    <thead>
                        <tr>
                            <th data-column-id="name" data-formatter="name" data-width="300px" ><?php echo __("Name"); ?></th>
                            <th data-column-id="description"><?php echo __("description"); ?></th>
                            <th data-column-id="commands" data-formatter="commands" data-sortable="false" data-width="150px"></th>
                        </tr>
                    </thead>
                </table>
                <div id="pluginsFormModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><?php echo __("Plugin Form"); ?></h4>
                            </div>
                            <div class="modal-body">
                                <form class="form-compact"  id="updatePluginForm" onsubmit="">
                                    <input type="hidden" id="inputPluginId"  >
                                    <label for="inputData" class="sr-only">Object Data</label>
                                    <textarea class="form-control" id="inputData"  rows="5"  placeholder="Object Data"></textarea>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __("Close"); ?></button>
                                <button type="button" class="btn btn-primary" id="savePluginBtn"><?php echo __("Save changes"); ?></button>
                            </div>
                        </div><!-- /.modal-content -->
                    </div><!-- /.modal-dialog -->
                </div>            
                <div id="pluginsImportFormModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <?php
                        $dir = "{$global['systemRootPath']}plugin";
                        if(!isUnzip()){
                            ?>                                
                            <div class="alert alert-warning">
                                Make sure you have the unzip app on your server 
                                <pre><code>sudo apt-get install unzip</code></pre>
                            </div>
                            <?php
                        }
                        if (is_writable($dir) ) {
                            ?>
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title"><?php echo __("Upload a Plugin ZIP File"); ?></h4>
                                </div>
                                <div class="modal-body">
                                    <input id="input-b1" name="input-b1" type="file" class="">
                                </div>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="alert alert-danger">
                                You need to make the plugin dir writable before upload, run this command and refresh this page
                                <pre><code>chown www-data:www-data <?php echo $dir; ?> && chmod 755 <?php echo $dir; ?></code></pre>
                            </div>
                            <?php
                        }
                        ?>
                    </div>                
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><a href="https://easytube.club/signUp" class="btn btn-default"><i class="fa fa-plug"></i> Easy Club Plugin Store </a></div>
                    <div class="panel-body">
                        <ul class="list-group" id="pluginStoreList">
                        </ul>
                    </div>
                </div>
            </div>
            <li class="list-group-item hidden" id="pluginStoreListModel">
                <img src="" class="img img-rounded img-responsive pull-left thumbnail zoom" style="max-width: 60px; margin-right: 5px;">
                <span class="label label-success">Price</span>
                <br>
                <a href="https://easytube.club/signUp" target="_blank"><strong>Plugin Name</strong> </a>  
                <a href="https://easytube.club/signUp" target="_blank" class="btn btn-default btn-sm"><i class="fa fa-cart-plus"></i> Buy This Plugin </a>              
                <p>Description</p>
            </li>

        </div><!--/.container-->
        <?php
        include 'include/footer.php';
        ?>
        <script>
            
            function createPluginStoreList(src, name, price, description) {
                var $li = $('#pluginStoreListModel').clone();
                $li.removeClass("hidden").attr("id", "");
                $li.find('img').attr("src", src);
                $li.find('strong').text(name);
                $li.find('span').text("USD $"+price);
                $li.find('p').text(description);
                $('#pluginStoreList').append($li);        

            }
            $(document).ready(function () {
                var grid = $("#grid").bootgrid({
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "pluginsAvailable.json"; ?>",
                    formatters: {
                        "commands": function (column, row) {
                            var editBtn = '';
                            if (row.id) {
                                editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit parameters</button>';
                            }
                            var sqlBtn = '';
                            if (row.databaseScript) {
                                sqlBtn = '<button type="button" class="btn btn-xs btn-default command-sql" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> Install tables</button>';
                            }
                            return  editBtn + "<br>" + sqlBtn;
                        },
                        "name": function (column, row) {
                            var checked = "";
                            if (row.enabled) {
                                checked = " checked='checked' ";
                            }
                            var switchBtn = '<div class="material-switch"><input name="enable' + row.uuid + '" id="enable' + row.uuid + '" type="checkbox" value="0" class="pluginSwitch" ' + checked + ' /><label for="enable' + row.uuid + '" class="label-success"></label></div>';

                            var txt = row.name + " (" + row.dir + ")<br><small class='text-muted'>UUID: " + row.uuid + "</small>";
                            txt += "<br>" + switchBtn;
                            return txt;
                        }
                    }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    /* Executes after data is loaded and rendered */
                    grid.find(".pluginSwitch").on("change", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        modal.showPleaseWait();
                        $.ajax({
                            url: 'switchPlugin',
                            data: {"uuid": row.uuid, "name": row.name, "dir": row.dir, "enable": $('#enable' + row.uuid).is(":checked")},
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                                $("#grid").bootgrid('reload');
                            }
                        });
                    });

                    grid.find(".command-edit").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        $('#inputPluginId').val(row.id);
                        $('#inputData').val(JSON.stringify(row.data_object));

                        $('#pluginsFormModal').modal();
                    });

                    grid.find(".command-sql").on("click", function (e) {
                        var row_index = $(this).closest('tr').index();
                        var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                        console.log(row);
                        $('#inputPluginId').val(row.id);
                        $('#inputData').val(JSON.stringify(row.data_object));
                        modal.showPleaseWait();
                        $.ajax({
                            url: 'runDBScriptPlugin.json',
                            data: {"name": row.name},
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                            }
                        });
                    });
                });


                $('#savePluginBtn').click(function (evt) {
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'addDataObjectPlugin.json',
                        data: {"id": $('#inputPluginId').val(), "object_data": $('#inputData').val()},
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            $("#grid").bootgrid('reload');
                            $('#pluginsFormModal').modal('hide');
                        }
                    });
                });
                $('#upload').click(function (evt) {
                    $('#pluginsImportFormModal').modal();
                });

                $('#input-b1').fileinput({
                    uploadUrl: '<?php echo $global['webSiteRootURL']; ?>pluginImport.json',
                    allowedFileExtensions: ['zip']
                }).on('fileuploaded', function (event, data, id, index) {
                    $("#grid").bootgrid('reload');
                    console.log('fileuploaded');
                    console.log(event);
                    console.log(data);
                    console.log(id);
                    console.log(index);
                });



                $.ajax({
                    url: 'https://easytube.club/plugins.json?jsonp=1',
                    dataType: 'jsonp', 
                    success: function (response) {
                        console.log(response);
                        for(i=0;i<response.rows.length;i++){
                            var r = response.rows[i];
                            createPluginStoreList(r.images[0], r.name, r.price, r.description);
                        }
                    }
                });
            });

        </script>
    </body>
</html>
