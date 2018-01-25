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
        <style>
            .panel{
                text-align: center;
            }
            .panel:hover { box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4), 0 1px 5px rgba(130, 130, 130, 0.35); }
            .panel-body
            {
                padding: 0px;
                text-align: center;
            }

            .the-price
            {
                background-color: rgba(220,220,220,.17);
                box-shadow: 0 1px 0 #dcdcdc, inset 0 1px 0 #fff;
                padding: 6px;
                margin: 0;
            }

            .the-price h1
            {
                line-height: 1em;
                padding: 0;
                margin: 0;
            }

            .subscript
            {
                font-size: 25px;
            }

            /* CSS-only ribbon styles    */
            .cnrflash
            {
                /*Position correctly within container*/
                position: absolute;
                top: -9px;
                right: 4px;
                z-index: 1; /*Set overflow to hidden, to mask inner square*/
                overflow: hidden; /*Set size and add subtle rounding  		to soften edges*/
                width: 100px;
                height: 100px;
                border-radius: 3px 5px 3px 0;
            }
            .cnrflash-inner
            {
                /*Set position, make larger then 			container and rotate 45 degrees*/
                position: absolute;
                bottom: 0;
                right: 0;
                width: 145px;
                height: 145px;
                -ms-transform: rotate(45deg); /* IE 9 */
                -o-transform: rotate(45deg); /* Opera */
                -moz-transform: rotate(45deg); /* Firefox */
                -webkit-transform: rotate(45deg); /* Safari and Chrome */
                -webkit-transform-origin: 100% 100%; /*Purely decorative effects to add texture and stuff*/ /* Safari and Chrome */
                -ms-transform-origin: 100% 100%;  /* IE 9 */
                -o-transform-origin: 100% 100%; /* Opera */
                -moz-transform-origin: 100% 100%; /* Firefox */
                background-image: linear-gradient(90deg, transparent 50%, rgba(255,255,255,.1) 50%), linear-gradient(0deg, transparent 0%, rgba(1,1,1,.2) 50%);
                background-size: 4px,auto, auto,auto;
                background-color: #aa0101;
                box-shadow: 0 3px 3px 0 rgba(1,1,1,.5), 0 1px 0 0 rgba(1,1,1,.5), inset 0 -1px 8px 0 rgba(255,255,255,.3), inset 0 -1px 0 0 rgba(255,255,255,.2);
            }
            .cnrflash-inner:before, .cnrflash-inner:after
            {
                /*Use the border triangle trick to make  				it look like the ribbon wraps round it's 				container*/
                content: " ";
                display: block;
                position: absolute;
                bottom: -16px;
                width: 0;
                height: 0;
                border: 8px solid #800000;
            }
            .cnrflash-inner:before
            {
                left: 1px;
                border-bottom-color: transparent;
                border-right-color: transparent;
            }
            .cnrflash-inner:after
            {
                right: 0;
                border-bottom-color: transparent;
                border-left-color: transparent;
            }
            .cnrflash-label
            {
                /*Make the label look nice*/
                position: absolute;
                bottom: 0;
                left: 0;
                display: block;
                width: 100%;
                padding-bottom: 5px;
                color: #fff;
                text-shadow: 0 1px 1px rgba(1,1,1,.8);
                font-size: 0.95em;
                font-weight: bold;
                text-align: center;
            }
            .panel-sm{
                margin-bottom: 5px;
            }
            .panel-sm table, .panel-sm div{
                margin: 0;
            }
            .panel-sm .panel-heading{
                height: 25px;
                padding: 2px 5px;
                font-size: 0.9em;
            }
            .panel-sm .panel-heading .panel-title{
                font-size: 0.9em;
            }
            .panel-sm .panel-body{
                padding: 0;
                font-size: 0.8em;
            }
        </style>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container-fluid">
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
                                        Make sure you have the unzip app on your server 
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
                                        You need to make the plugin dir writable before upload, run this command and refresh this page
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
                            <div class="panel-heading"><a href="https://easytube.club/signUp" class="btn btn-default btn-xs"><i class="fa fa-plug"></i> Easy Club Plugin Store </a></div>
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
                        <a href="https://easytube.club/signUp" class="btn btn-success btn-xs" role="button"><i class="fa fa-cart-plus"></i> Buy This Plugin </a>
                    </div>
                </div>
            </li>

        </div><!--/.container-->
        <?php
        include 'include/footer.php';
        ?>

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
                        if(val.type === 'textarea'){
                            input = $('<textarea />', {"class": 'form-control jsonElement', "name": i, "pluginType":"object"});
                            
                            input.text(val.value);
                        }else{
                            input = $('<input />', {"class": 'form-control jsonElement', "type": val.type, "name": i, "value": val.value, "pluginType":"object"});
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
                    $('.jsonElement').change(function(){
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
                    if(pluginType==='object'){
                        if(typeof type === 'undefined'){
                            type = 'textarea';
                        }
                        json [name] = {type:type, value:$(this).val()};
                    }else if (type === 'checkbox') {
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
                    navigation: 0,
                    ajax: true,
                    url: "<?php echo $global['webSiteRootURL'] . "pluginsAvailable.json"; ?>",
                    formatters: {
                        "commands": function (column, row) {
                            var editBtn = '';

                            if (row.id && !$.isEmptyObject(row.data_object)) {
                                editBtn = '<button type="button" class="btn btn-xs btn-default command-edit" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit parameters</button>';
                            }
                            var sqlBtn = '';
                            if (row.databaseScript) {
                                sqlBtn = '<button type="button" class="btn btn-xs btn-default command-sql" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> Install tables</button>';
                            }
                            return  editBtn + "<br>" + sqlBtn + "<br>" + row.pluginMenu;
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
                });
                $.ajax({
                    url: 'https://easytube.club/plugins.json?jsonp=1',
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
    </body>
</html>
