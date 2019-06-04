
<div class="container-fluid">
    <?php
    include $global['systemRootPath'] . 'view/include/updateCheck.php';
    ?>
    <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#menu0"><i class="fa fa-plug"></i> Installed Plugins</a></li>
        <li><a data-toggle="tab" href="#menu1"><i class="fa fa-cart-plus"></i> Plugins Store</a></li>
    </ul>

    <div class="tab-content">
        <div id="menu0" class="tab-pane fade in active">
            <div class="list-group-item">
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
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#visual">Visual</a></li>
                                    <li><a data-toggle="tab" href="#code">Code</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="visual" class="tab-pane fade in active">
                                        <div class="row" id="jsonElements" style="padding: 10px;">Some content.</div>
                                    </div>
                                    <div id="code" class="tab-pane fade">
                                        <form class="form-compact"  id="updatePluginForm" onsubmit="">
                                            <input type="hidden" id="inputPluginId"  >
                                            <label for="inputData" class="sr-only">Object Data</label>
                                            <textarea class="form-control" id="inputData"  rows="5"  placeholder="Object Data"></textarea>
                                        </form>
                                    </div>
                                </div>
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
                        if (!isUnzip()) {
                            ?>
                            <div class="alert alert-warning">
                                <?php echo __("Make sure you have the unzip app on your server"); ?>
                                <pre><code>sudo apt-get install unzip</code></pre>
                            </div>
                            <?php
                        }
                        if (is_writable($dir)) {
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
                                <?php echo __("You need to make the plugin dir writable before upload, run this command and refresh this page"); ?>
                                <pre><code>chown www-data:www-data <?php echo $dir; ?> && chmod 755 <?php echo $dir; ?></code></pre>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="menu1" class="tab-pane fade">
            <div class="list-group-item">
                <div class="panel panel-default">
                    <div class="panel-heading"><a href="https://www.youphptube.com/plugins/" class="btn btn-default btn-xs"><i class="fa fa-plug"></i> Plugin Store </a></div>
                    <div class="panel-body">
                        <ul class="list-group" id="pluginStoreList">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <li class="list-group-item hidden col-md-3" id="pluginStoreListModel">

        <div class="panel panel-warning panel-sm">
            <div class="panel-heading">
                <h3 class="panel-title"></h3>
            </div>
            <div class="panel-body">
                <div class="the-price">
                    <h1>
                        USD $<span class="int">0</span>.<small class="cents">00</small>
                    </h1>
                </div>
                <table class="table">
                    <tr >
                        <td>
                            <img src="" class="img img-responsive img-rounded img-thumbnail zoom" style="height: 70px;">
                        </td>
                    </tr>
                    <tr class="active">
                        <td class="desc" style="height: 50px;"></td>
                    </tr>
                </table>
            </div>
            <div class="panel-footer">
                <a href="https://www.youphptube.com/plugins/" class="btn btn-success btn-xs" role="button"><i class="fa fa-cart-plus"></i> <?php echo __("Buy This Plugin"); ?> </a>
            </div>
        </div>
    </li>

</div><!--/.container-->

<script>
    function jsonToForm(json) {
        $('#jsonElements').empty();
        $.each(json, function (i, val) {
            var div;
            var label;
            var input;
            if (typeof (val) === "object") {// checkbox
                div = $('<div />', {"class": 'form-group'});
                label = $('<label />', {"text": i + ": "});
                if (val.type === 'textarea') {
                    input = $('<textarea />', {"class": 'form-control jsonElement', "name": i, "pluginType": "object"});

                    input.text(val.value);
                } else {
                    input = $('<input />', {"class": 'form-control jsonElement', "type": val.type, "name": i, "value": val.value, "pluginType": "object"});
                }
                div.append(label);
                div.append(input);
            } else if (typeof (val) === "boolean") {// checkbox
                div = $('<div />', {"class": 'form-group'});
                label = $('<label />', {"class": "checkbox-inline"});
                input = $('<input />', {"class": 'jsonElement', "type": 'checkbox', "name": i, "value": 1, "checked": val});
                label.append(input);
                label.append(" " + i);
                div.append(label);
            } else {
                div = $('<div />', {"class": 'form-group'});
                label = $('<label />', {"text": i + ": "});
                input = $('<input />', {"class": 'form-control jsonElement', "name": i, "type": 'text', "value": val});
                div.append(label);
                div.append(input);
            }
            $('#jsonElements').append(div);
            $('.jsonElement').change(function () {
                var json = formToJson();
                json = JSON.stringify(json);
                $('#inputData').val(json);
            });
        })
    }

    function formToJson() {
        var json = {};
        $(".jsonElement").each(function (index) {
            var name = $(this).attr("name");
            var type = $(this).attr("type");
            var pluginType = $(this).attr("pluginType");
            if (pluginType === 'object') {
                if (typeof type === 'undefined') {
                    type = 'textarea';
                }
                json [name] = {type: type, value: $(this).val()};
            } else if (type === 'checkbox') {
                json [name] = $(this).is(":checked");
            } else {
                json [name] = $(this).val();
            }
        });
        console.log(json);
        return json;
    }

    function createPluginStoreList(src, name, price, description) {
        var intPrice = Math.floor(price);
        //var cents = Math.ceil((price - intPrice) * 100);
        var $li = $('#pluginStoreListModel').clone();
        $li.removeClass("hidden").attr("id", "");
        $li.find('.panel-title').text(name);
        $li.find('.int').text(intPrice);
        $li.find('.cents').text("99");
        $li.find('.desc').text(description);
        $li.find('.img').attr("src", src);
        $('#pluginStoreList').append($li);
    }
    $(document).ready(function () {
        var myTextarea = document.getElementById("inputData");
        var grid = $("#grid").bootgrid({
            labels: {
                noResults: "<?php echo __("No results found!"); ?>",
                all: "<?php echo __("All"); ?>",
                infos: "<?php echo __("Showing {{ctx.start}} to {{ctx.end}} of {{ctx.total}} entries"); ?>",
                loading: "<?php echo __("Loading..."); ?>",
                refresh: "<?php echo __("Refresh"); ?>",
                search: "<?php echo __("Search"); ?>",
            },
            navigation: 0,
            ajax: true,
            url: "<?php echo $global['webSiteRootURL'] . "objects/pluginsAvailable.json.php"; ?>",
            formatters: {
                "commands": function (column, row) {
                    var editBtn = '';

                    if (row.id && !$.isEmptyObject(row.data_object)) {
                        editBtn = '<button type="button" class="btn btn-xs btn-default command-edit  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit parameters</button>';
                    }
                    var sqlBtn = '';
                    if (row.databaseScript) {
                        sqlBtn = '<button type="button" class="btn btn-xs btn-default command-sql  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> Install tables</button>';
                    }
                    menu = '';
                    if (row.installedPlugin && row.installedPlugin.status == 'active') {
                        menu = row.pluginMenu;
                    }
                    updateBtn = '';
                    if (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion") && row.installedPlugin.pluginversion != row.pluginversion) {
                        updateBtn = '<button type="button" class="btn btn-xs btn-warning command-update  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Update Script"><span class="fa fa-wrench" aria-hidden="true"></span> Update @' + row.pluginversion + '</button>';
                    }

                    return  editBtn + sqlBtn + updateBtn + "<br>" + menu;
                },
                "name": function (column, row) {
                    var checked = "";
                    if (row.enabled) {
                        checked = " checked='checked' ";
                    }
                    var switchBtn = '<div class="material-switch"><input name="enable' + row.uuid + '" id="enable' + row.uuid + '" type="checkbox" value="0" class="pluginSwitch" ' + checked + ' /><label for="enable' + row.uuid + '" class="label-success"></label></div>';
                    var tags = '';
                    if (row.tags) {
                        for (i = 0; i < row.tags.length; i++) {
                            var cl = "primary";
                            if (row.tags[i] === 'free') {
                                cl = 'success';
                            } else if (row.tags[i] === 'firstPage') {
                                cl = 'danger';
                            } else if (row.tags[i] === 'login') {
                                cl = 'info';
                            }

                            tags += '<span class="label label-' + cl + '">' + row.tags[i] + '</span> ';
                        }
                    }



                    var txt = row.name + " (" + row.dir + ")<br><small class='text-muted'>UUID: " + row.uuid + "</small>";
                    if (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion")) {
                        console.log("Objecto: " + row.name);
                        console.log("Installed: " + row.installedPlugin.pluginversion);
                        console.log("Object: " + row.pluginversion);
                        console.log(row.installedPlugin.pluginversion != row.pluginversion);
                        if (row.installedPlugin.pluginversion != row.pluginversion) {
                            txt += "<br><small class='text-danger'>Installed (@" + row.installedPlugin.pluginversion + ")<br>Current Version (@" + row.pluginversion + "), please update</small><br>";
                        } else {
                            txt += "<br><small class='text-success'>Version: @" + row.pluginversion + "</small><br>";
                        }
                    }
                    console.log(txt);
                    txt += "<br>" + switchBtn;
                    txt += "<br>" + tags;
                    return txt;
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            /* Executes after data is loaded and rendered */
            grid.find(".pluginSwitch").on("change", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginSwitch.json.php',
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
                $('#inputPluginId').val(row.id);
                var json = JSON.stringify(row.data_object);
                jsonToForm(row.data_object);
                $('#inputData').val(json);
                $('#pluginsFormModal').modal();
            });
            grid.find(".command-sql").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                $('#inputPluginId').val(row.id);
                $('#inputData').val(JSON.stringify(row.data_object));
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginRunDatabaseScript.json.php',
                    data: {"name": row.name},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                    }
                });
            });
            grid.find(".command-update").on("click", function (e) {
                var row_index = $(this).closest('tr').index();
                var row = $("#grid").bootgrid("getCurrentRows")[row_index];
                $('#inputPluginId').val(row.id);
                $('#inputData').val(JSON.stringify(row.data_object));
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginRunUpdateScript.json.php',
                    data: {"name": row.name},
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        $("#grid").bootgrid('reload');
                    }
                });
            });
        });
        $('#savePluginBtn').click(function (evt) {
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/pluginAddDataObject.json.php',
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
            uploadUrl: '<?php echo $global['webSiteRootURL']; ?>objects/pluginImport.json.php',
            allowedFileExtensions: ['zip']
        }).on('fileuploaded', function (event, data, id, index) {
            $("#grid").bootgrid('reload');
        });
        $.ajax({
            url: 'https://www.youphptube.com/plugins/plugins.json?jsonp=1',
            dataType: 'jsonp',
            success: function (response) {
                for (i = 0; i < response.rows.length; i++) {
                    var r = response.rows[i];
                    createPluginStoreList(r.images[0], r.name, r.price, r.description);
                }
            }
        });
    });

</script>