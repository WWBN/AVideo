<?php
$uuids = AVideoPlugin::getPluginsOnByDefault();
$rowId = [];
foreach ($uuids as $value) {
    $rowId[] = " row.uuid != '{$value}' ";
}
$uuidJSCondition = implode(" && ", $rowId);
$wwbnIndexPlugin = AVideoPlugin::isEnabledByName('WWBNIndex');
?>
<style>
    /* .pluginDescription uses white-space:nowrap for its ellipsis clipping, which (with the
       default table-layout:auto) forces the whole column to the description's full unwrapped
       width; table-layout:fixed makes the column widths declared on <th> authoritative instead */
    #grid {
        table-layout: fixed;
    }

    td.wrapText {
        white-space: normal;
        word-break: break-word;
    }

    .PluginActive,
    .PluginTags {
        border: solid 2px;
    }

    .PluginActive.checked,
    .PluginTags.checked {}

    .PluginActive.unchecked,
    .PluginTags.unchecked {
        background-color: rgba(0, 0, 0, 0.4);
    }

    .PluginActive:hover,
    .PluginTags:hover {
        border: solid 2px rgba(0, 0, 0, 1);
        cursor: pointer;
    }

    .pluginDescription {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        height: 1.75em;
        line-height: 1.75;
    }

    #jsonElements .is_deprecated,
    #jsonElements .is_experimental,
    #jsonElements .is_advanced {
        display: none;
        padding: 5px;
    }

    #jsonElements .is_deprecated.forceShow,
    #jsonElements .is_experimental.forceShow,
    #jsonElements .is_advanced.forceShow {
        display: block;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading tabbable-line">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#menu0"><i class="fa fa-plug"></i> <?php echo __('Installed Plugins'); ?></a></li>
                <li><a data-toggle="tab" href="#menu1"><i class="fa fa-cart-plus"></i> <?php echo __('Plugins Store'); ?></a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                <div id="menu0" class="tab-pane fade in active">
                    <div class="panel panel-default">
                        <div class="panel-heading ">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default" id="upload">
                                    <i class="fas fa-plus"></i> <?php echo __("Upload a Plugin"); ?>
                                </button>
                                <button type="button" class="btn btn-primary" id="createPlugin" onclick="avideoModalIframeFull(webSiteRootURL + 'CreatePlugin/');">
                                    <i class="fas fa-plus-circle"></i> <?php echo __("Create a Plugin"); ?>
                                </button>
                                <button type="button" class="btn btn-warning" id="updateAllPluginsBtn">
                                    <i class="fa fa-wrench"></i> <?php echo __("Update All Plugins"); ?>
                                </button>
                            </div>
                            <div style="text-align: right; padding: 5px;">
                                <span class="badge" id="PluginTagsTotal">...</span>
                                <button class="label label-default checked PluginTags PluginActive" pluginTag="all" id="PluginTagsAll" onclick="resetShowActiveInactiveOnly();PluginTagsReset();">
                                    <i class="fas fa-check-double"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("All"); ?></span>
                                </button>
                                <button class="label label-primary checked PluginActive" pluginTag="Installed" id="PluginTagsInstalled" onclick="showActivesOnly();">
                                    <i class="fas fa-check"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Installed"); ?></span>
                                </button>
                                <button class="label label-primary checked PluginActive" pluginTag="Uninstalled" id="PluginTagsUninstalled" onclick="showInactiveOnly();">
                                    <i class="fas fa-times"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Uninstalled"); ?></span>
                                </button>
                                <?php
                                $class = new ReflectionClass('PluginTags');
                                $staticProperties = $class->getStaticProperties();
                                foreach ($staticProperties as $key => $value) {
                                ?>
                                    <button class="label label-<?php echo $value[0]; ?> unchecked PluginTags" id="PluginTags<?php echo $value[3]; ?>" pluginTag="<?php echo $value[3]; ?>" onclick="PluginTagsToggle('<?php echo $value[3]; ?>')" data-toggle="tooltip" title="<?php echo __($value[1]); ?>">
                                        <?php echo $value[2]; ?> <span class="hidden-md hidden-sm hidden-xs"><?php echo __($value[1]); ?></span>
                                    </button>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="panel-body ">
                            <div class="table-responsive">
                            <table id="grid" class="table table-condensed table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th data-column-id="name" class="" data-width="300px"><?php echo __("Name"); ?></th>
                                        <th data-column-id="description" class="wrapText hidden-md hidden-sm hidden-xs"><?php echo __("description"); ?></th>
                                        <th data-column-id="modified" class="hidden-sm hidden-xs" data-width="180px"><?php echo __("Modified"); ?></th>
                                        <th data-column-id="commands" class="" data-sortable="false" data-width="150px"></th>
                                    </tr>
                                </thead>
                            </table>
                            </div>
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
                                                <li class="pull-right">
                                                    <label>
                                                        <input type="checkbox" id="is_advanced" onclick="tooglePluginForceShow(this);">
                                                        <?php echo __('Show Advanced Options'); ?>
                                                        <span class="badge">0</span>
                                                    </label>
                                                    <div class="clearfix"></div>
                                                    <label>
                                                        <input type="checkbox" id="is_deprecated" onclick="tooglePluginForceShow(this);">
                                                        <?php echo __('Show Deprecated Options'); ?>
                                                        <span class="badge">0</span>
                                                    </label>
                                                    <div class="clearfix"></div>
                                                    <label>
                                                        <input type="checkbox" id="is_experimental" onclick="tooglePluginForceShow(this);">
                                                        <?php echo __('Show Experimental Options'); ?>
                                                        <span class="badge">0</span>
                                                    </label>
                                                </li>
                                            </ul>
                                            <div class="tab-content">
                                                <div id="visual" class="tab-pane fade in active">
                                                    <div class="row" id="jsonElements" style="padding: 10px;">Some content.</div>
                                                </div>
                                                <div id="code" class="tab-pane fade">
                                                    <form class="form-compact" id="updatePluginForm" onsubmit="">
                                                        <input type="hidden" id="inputPluginId">
                                                        <label for="inputData" class="sr-only">Object Data</label>
                                                        <textarea class="form-control" id="inputData" rows="5" placeholder="Object Data"></textarea>
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
                        </div>
                    </div>
                </div>
                <div id="menu1" class="tab-pane fade">
                    <div class="panel panel-default">
                        <div class="panel-heading clearfix">
                            <div class="btn-group pull-right" role="group" aria-label="Plugin Store Actions">
                                <a href="https://tutorials.avideo.com/signUp" class="btn btn-success btn-sm" target="_top">
                                    <i class="fa fa-user-plus"></i> Register
                                </a>
                                <a href="https://streamphp.com/marketplace/" class="btn btn-info btn-sm" target="_top">
                                    <i class="fa fa-sign-in"></i> Log In
                                </a>
                                <a href="https://streamphp.com/marketplace/?tab=plugin" class="btn btn-primary btn-sm" target="_top">
                                    <i class="fa fa-plug"></i> Visit Plugin Store
                                </a>
                            </div>
                            <p>Access our Plugin Store to explore, purchase, or upgrade plugins for your AVideo platform.</p>
                            <p>
                                <strong>Need an account?</strong><br>
                                Register a new account or log in to your existing one
                            </p>

                        </div>
                        <div class="panel-body">

                            <ul class="list-group" id="pluginStoreList">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hidden col-md-3" id="pluginStoreListModel">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h3 class="panel-title"></h3>
                    </div>
                    <div class="panel-body" style="padding: 0;">
                        <div class="the-price">
                            <h1>
                                USD $<span class="int">0</span>.<small class="cents">00</small>
                            </h1>
                        </div>
                        <table class="table" style="margin: 0;">
                            <tr>
                                <td class="center" style="text-align: center; vertical-align: middle; height: 70px;">
                                    <img src="" class="img img-responsive img-rounded img-thumbnail zoom" style="height: 70px;">
                                </td>
                            </tr>
                            <tr class="active">
                                <td class="desc" style="height: 50px;"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <a href="https://streamphp.com/marketplace/?tab=plugin" class="btn btn-success btn-xs" role="button"><i class="fa fa-cart-plus"></i> <?php echo __("Buy This Plugin"); ?> </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!--/.container-->

<div id="pluginsPermissionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div id="pluginsPermissionModalContent">

        </div>
    </div>
</div>
<script src="<?php echo getURL('view/js/form2JSON.js'); ?>" type="text/javascript"></script>
<script>
    var globalToken = '<?php echo getToken(300); ?>';

    function showActivesOnly() {
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('checked');
        $(id).addClass('unchecked');
        var id = "#PluginTagsInstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        processShow();
    }

    function showInactiveOnly() {
        var id = "#PluginTagsInstalled";
        $(id).removeClass('checked');
        $(id).addClass('unchecked');
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        processShow();
    }

    function resetShowActiveInactiveOnly() {
        var id = "#PluginTagsInstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
        var id = "#PluginTagsUninstalled";
        $(id).removeClass('unchecked');
        $(id).addClass('checked');
    }

    function showAllOnProcess() {
        if (isFilterInstalledUninstalledDisabled() || $("#PluginTagsAll").hasClass('checked')) {
            return true;
        }
        return false;
    }

    function isFilterInstalledUninstalledDisabled() {
        var id1 = "#PluginTagsInstalled";
        var id2 = "#PluginTagsUninstalled";
        if ($(id1).hasClass('checked') && $(id2).hasClass('checked')) {
            return false;
        }
        if ($(id1).hasClass('unchecked') && $(id2).hasClass('unchecked')) {
            return false;
        }
        return true;
    }

    // tbody-only data rows: excludes the thead row (a bare "#grid tr" selector matches it too,
    // which used to hide the header itself) and any DataTables "no data available" placeholder row
    function getGridDataRows() {
        return $('#grid tbody tr').not(':has(td.dataTables_empty)');
    }

    function processShowHideIfActive(tr) {
        if ($(tr).find(".pluginSwitch").is(":checked")) {
            if ($("#PluginTagsInstalled").hasClass('checked')) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        } else {
            if ($("#PluginTagsInstalled").hasClass('checked')) {
                $(tr).hide();
            } else {
                $(tr).show();
            }
        }
    }

    function processShow() {
        if (!isFilterInstalledUninstalledDisabled()) {
            PluginTagsProcess();
        } else {
            var allItemsSeletors = getAllItemsSelector();
            //console.log(allItemsSeletors);
            getGridDataRows().each(function(i, tr) {

                if (allItemsSeletors) {
                    if ($(tr).find(allItemsSeletors).length !== 0) {
                        processShowHideIfActive(tr);
                    } else {
                        $(tr).hide();
                    }
                } else {
                    processShowHideIfActive(tr);
                }


                //console.log($(tr).find(allItemsSeletors).length);
                if (!allItemsSeletors || $(tr).find(allItemsSeletors).length !== 0) {
                    if ($(tr).find(".pluginSwitch").is(":checked")) {
                        if ($("#PluginTagsInstalled").hasClass('checked')) {
                            $(tr).show();
                        } else {
                            $(tr).hide();
                        }
                    } else {
                        if ($("#PluginTagsInstalled").hasClass('checked')) {
                            $(tr).hide();
                        } else {
                            $(tr).show();
                        }
                    }
                }
            });
        }

        totalVisible();
    }

    function PluginTagsReset() {
        $('.PluginTags').not('#PluginTagsAll').removeClass('checked');
        $('.PluginTags').not('#PluginTagsAll').addClass('unchecked');
        $("#PluginTagsAll").removeClass('unchecked');
        $("#PluginTagsAll").addClass('checked');
        getGridDataRows().show();
        totalVisible();
    }

    function totalVisible() {
        var rows = getGridDataRows();
        $('#PluginTagsTotal').text(rows.filter(':visible').length + ' / ' + rows.length);
    }

    function PluginTagsToggle(type) {
        var id = '#PluginTags' + type;
        if ($(id).hasClass('checked')) {
            $(id).removeClass('checked');
            $(id).addClass('unchecked');
        } else {
            $(id).removeClass('unchecked');
            $(id).addClass('checked');
        }
        $('#PluginTagsAll').removeClass('checked');
        $('#PluginTagsAll').addClass('unchecked');
        processShow();
    }

    function getAllItemsSelector() {
        var selectors = [];
        $('.PluginTags').each(function(i, obj) {
            if ($(obj).hasClass('checked')) {
                selectors.push('.plugin' + $(obj).attr('pluginTag'));
            }
        });
        if ($("#PluginTagsAll").hasClass('checked') || selectors.length === 0) {
            PluginTagsReset();
            return false;
        }
        return selectors.join(", ");
    }

    function PluginTagsProcess() {
        var allItemsSeletors = getAllItemsSelector();
        getGridDataRows().each(function(i, tr) {
            if (!allItemsSeletors || $(tr).find(allItemsSeletors).length !== 0) {
                $(tr).show();
            } else {
                $(tr).hide();
            }
        });
        totalVisible();
    }

    function tooglePluginDescription(t) {
        if ($(t).parent().hasClass('pluginDescription')) {
            $(t).parent().removeClass('pluginDescription');
            $(t).find('i').removeClass('fa-plus');
            $(t).find('i').addClass('fa-minus');
        } else {
            $(t).parent().addClass('pluginDescription');
            $(t).find('i').addClass('fa-plus');
            $(t).find('i').removeClass('fa-minus');
        }
    }


    function tooglePluginForceShow(t) {
        var id = $(t).attr('id');
        var selector = '#jsonElements .' + id;
        if ($(t).is(":checked")) {
            $(selector).addClass('forceShow');
            avideoTooltip(selector, id.replace('_', ' ').toUpperCase());
        } else {
            $(selector).removeClass('forceShow');
        }
    }

    function pluginPermissionsBtn(plugins_id) {
        modal.showPleaseWait();
        $("#pluginsPermissionModalContent").html('');
        $.ajax({
            url: webSiteRootURL + 'plugin/Permissions/getPermissionsFromPlugin.html.php?plugins_id=' + plugins_id,
            success: function(response) {
                modal.hidePleaseWait();
                $("#pluginsPermissionModalContent").html(response);
                $('#pluginsPermissionModal').modal();
            }
        });
    }
    var panelCount = 0;
    $(document).ready(function() {


        var myTextarea = document.getElementById("inputData");
        // $('#grid').bootgrid('reload'|'getCurrentRows') calls below are translated onto this DataTable
        // by the shared bridge in view/js/avideoDataTable.js (loaded via Page::loadBasicCSSAndJS()).

        var pluginFormatters = {
                "commands": function(column, row) {
                    var editBtn = '';

                    if (row.id && !$.isEmptyObject(row.data_object)) {
                        editBtn = '<button type="button" class="btn btn-xs btn-default command-edit  btn-block" data-row-id="' + row.id + '" data-pname="' + row.name + '" data-toggle="tooltip" data-placement="left" title="Edit"><i class="fa-solid fa-pen-to-square"></i> <?php echo __('Edit parameters'); ?></button>';
                    }
                    var sqlBtn = '';
                    if (row.databaseScript && row.isPluginTablesInstalled) {
                        //sqlBtn = '<button type="button" class="btn btn-xs btn-default command-sql  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="left" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> <?php echo __('Reinstall tables'); ?></button>';
                    }
                    menu = '';
                    if (row.installedPlugin && row.installedPlugin.status == 'active') {
                        menu = row.pluginMenu;
                    }

                    return editBtn + sqlBtn + menu;
                },
                "name": function(column, row) {
                    var checked = '';
                    var switchBtn = '';
                    if (<?php echo $uuidJSCondition; ?>) {
                        if (row.isPluginTablesInstalled || !row.databaseScript || (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion"))) {
                            if (row.enabled) {
                                checked = " checked='checked' ";
                            }
                            switchBtn = '<div class="material-small material-switch pull-left"  style="direction: ltr;"><input name="enable' + row.uuid + '" id="enable' + row.uuid + '" type="checkbox" value="0" class="pluginSwitch" data-pname="' + row.name + '" ' + checked + ' /><label for="enable' + row.uuid + '" class="label-success"></label></div>';
                        }

                    } else {
                        if (!row.enabled) {
                            $.ajax({
                                url: webSiteRootURL + 'objects/pluginSwitch.json.php',
                                data: {
                                    "uuid": row.uuid,
                                    "name": row.name,
                                    "dir": row.dir,
                                    "enable": true,
                                    "globalToken": globalToken
                                },
                                type: 'post',
                                success: function(response) {}
                            });
                        }
                        switchBtn = '';
                    }
                    if (!row.isPluginTablesInstalled) {
                        switchBtn += '<button type="button" class="btn btn-xs btn-danger command-sql  btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="right" title="Run Database Script"><span class="fa fa-database" aria-hidden="true"></span> <?php echo __('Install tables'); ?></button>';
                    }
                    //var txt = '<span id="plugin' + row.uuid + '" style="margin-top: -60px; position: absolute;"></span><a href="#plugin' + row.uuid + '">' + row.name + "</a> (" + row.dir + ")<br><small class='text-muted'>UUID: " + row.uuid + "</small>";
                    var txt = '<span id="plugin' + row.uuid + '" style="margin-top: -60px; position: absolute;"></span><a href="#plugin' + row.uuid + '">' + row.name + "</a> <small class='text-muted'>(" + row.dir + ")</small>";


                    txt += "<br> " + switchBtn + ' &nbsp; ';
                    if (row.hasOwnProperty("installedPlugin") && row.installedPlugin.hasOwnProperty("pluginversion")) {
                        //console.log("Objecto: " + row.name);
                        //console.log("Installed: " + row.installedPlugin.pluginversion);
                        //console.log("Object: " + row.pluginversion);
                        //console.log(row.installedPlugin.pluginversion != row.pluginversion);
                        if (row.installedPlugin.pluginversion != row.pluginversion) {
                            txt += "<small class='text-danger'>Installed (@" + row.installedPlugin.pluginversion + ")<br>Current Version (@" + row.pluginversion + "), please update</small>";
                            txt += '<div class="clearfix"></div><button type="button" class="btn btn-xs btn-warning command-update btn-block" data-row-id="' + row.id + '" data-toggle="tooltip" data-placement="right" title="<?php echo __('Run Update Script'); ?>"><span class="fa fa-wrench" aria-hidden="true"></span> <?php echo __('Update'); ?> @' + row.pluginversion + '</button>';
                        } else {
                            txt += "<small class='text-success'>Version: @" + row.pluginversion + "</small>";
                        }
                    }
                    if (row.hasOwnProperty("permissions") && row.permissions.length) {
                        var disabled = '';
                        if (!row.isPluginTablesInstalled) {
                            disabled = ' disabled="disabled" ';
                        }
                        txt += '<button ' + disabled + ' type="button" class="btn btn-xs btn-default btn-block" onclick="pluginPermissionsBtn(' + row.id + ')" data-toggle="tooltip" data-placement="right" title="<?php echo __('User Groups Permissions'); ?>"><span class="fa fa-users" aria-hidden="true"></span> <?php echo __('User Groups Permissions'); ?></button>';
                    }

                    return txt;
                },
                "description": function(column, row) {
                    var txt = '<div class="pluginDescription"><button class="btn btn-xs btn-default" onclick="tooglePluginDescription(this);"><i class="fas fa-plus"></i></button> ' + row.description + '</div>';
                    var tags = '';
                    if (row.tags) {
                        for (i = 0; i < row.tags.length; i++) {
                            if (typeof row.tags[i] == 'object') {
                                tags += '<span class="label label-' + row.tags[i][0] + ' plugin' + row.tags[i][3] + '">' + row.tags[i][2] + ' ' + row.tags[i][1] + '</span> ';
                            } else {
                                if (row.tags[i] === 'update') {
                                    tags += '<a class="label label-warning" href="https://streamphp.com/marketplace/" target="_blank">Update Available: v' + row.pluginversionMarketPlace + '</a> ';
                                } else {
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
                        }
                    }
                    txt += "<br>" + tags;
                    return txt;
                },
                "modified": function(column, row) {
                    if (!row.modified || row.modified == '' || row.modified == '0000-00-00 00:00:00') {
                        return '<small class="text-muted"><i class="fas fa-minus"></i></small>';
                    }
                    var date = new Date(row.modified);
                    var now = new Date();
                    var diff = Math.floor((now - date) / 1000); // diferença em segundos
                    var timeAgo = '';

                    if (diff < 60) {
                        timeAgo = '<?php echo __("just now"); ?>';
                    } else if (diff < 3600) {
                        var mins = Math.floor(diff / 60);
                        timeAgo = mins + ' <?php echo __("min ago"); ?>';
                    } else if (diff < 86400) {
                        var hours = Math.floor(diff / 3600);
                        timeAgo = hours + ' <?php echo __("hours ago"); ?>';
                    } else if (diff < 2592000) {
                        var days = Math.floor(diff / 86400);
                        timeAgo = days + ' <?php echo __("days ago"); ?>';
                    } else {
                        timeAgo = date.toLocaleDateString();
                    }

                    var formattedDate = date.toLocaleString();
                    return '<small title="' + formattedDate + '" data-toggle="tooltip"><i class="far fa-clock"></i> ' + timeAgo + '</small>';
                }
        };

        // objects/pluginsAvailable.json.php always returns every plugin in one shot (no real pagination/search/sort),
        // and the tag-filter buttons (PluginTagsToggle/processShow/etc.) show/hide <tr> elements directly by hand,
        // so every row must stay rendered in the DOM at once - matching the old bootgrid navigation:0 (paging: false).
        avideoDataTable("#grid", {
            paging: false,
            dom: 't',
            autoWidth: false,
            columns: [
                { data: null, className: '', width: '300px', render: function(data, type, row) { return pluginFormatters.name(null, row); } },
                { data: null, className: 'wrapText hidden-md hidden-sm hidden-xs', render: function(data, type, row) { return pluginFormatters.description(null, row); } },
                {
                    data: 'modified', className: 'hidden-sm hidden-xs', width: '180px',
                    render: function(data, type, row) {
                        // sort/type need a real timestamp - the "X days ago" display text has no chronological meaning
                        if (type === 'sort' || type === 'type') {
                            if (!row.modified || row.modified === '' || row.modified === '0000-00-00 00:00:00') {
                                return -1;
                            }
                            var t = new Date(row.modified).getTime();
                            return isNaN(t) ? -1 : t;
                        }
                        return pluginFormatters.modified(null, row);
                    }
                },
                { data: null, orderable: false, className: '', width: '150px', render: function(data, type, row) { return pluginFormatters.commands(null, row); } }
            ],
            ajax: function(data, callback, settings) {
                $.ajax({
                    url: "<?php echo $global['webSiteRootURL'] . "objects/pluginsAvailable.json.php"; ?>",
                    dataType: 'json',
                    success: function(json) {
                        callback({ data: json.rows });
                    }
                });
            },
        });

        // Delegated, one-time bindings (namespaced) instead of grid.find(...).on(...) inside draw.dt: DataTables
        // reuses/reorders the same <tr> elements across sorts, so re-binding on every draw stacked a new handler
        // each time, firing the AJAX call / opening the modal once per past sort for a single click.
        var grid = $("#grid");
        grid.off('change.pluginmgr', '.pluginSwitch').on('change.pluginmgr', '.pluginSwitch', function(e) {
            var row = grid.DataTable().row($(this).closest('tr')).data();
            var this_ = $(this);
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/pluginSwitch.json.php',
                data: {
                    "uuid": row.uuid,
                    "name": row.name,
                    "dir": row.dir,
                    "enable": $('#enable' + row.uuid).is(":checked"),
                    "globalToken": globalToken
                },
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                    if (this_.data("pname") == "WWBNIndex") {
                        $.ajax({
                            url: "<?= $global['webSiteRootURL']; ?>plugin/WWBNIndex/ajax.php",
                            data: {
                                "action": "changePluginStatus",
                                "enabled": this_.is(":checked")
                            },
                            type: "post",
                            success: function(response) {
                                window.location.reload();
                            }
                        });
                    } else {
                        $("#grid").bootgrid('reload');
                    }
                }
            });
        });
        grid.off('click.pluginmgr', '.command-edit').on('click.pluginmgr', '.command-edit', function(e) {
            var row = grid.DataTable().row($(this).closest('tr')).data();
            $('#inputPluginId').val(row.id);
            var json = JSON.stringify(row.data_object);
            //console.log(json);
            //console.log(row.data_object);
            jsonToForm(row.data_object, row.data_object_helper, row.data_object_info);
            $('#inputData').val(json);
            $('#pluginsFormModal').modal();
            $('#is_advanced').prop('checked', false);
        });
        grid.off('click.pluginmgr', '.command-sql').on('click.pluginmgr', '.command-sql', function(e) {
            var row = grid.DataTable().row($(this).closest('tr')).data();
            $('#inputPluginId').val(row.id);
            $('#inputData').val(JSON.stringify(row.data_object));
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/pluginRunDatabaseScript.json.php',
                data: {
                    "name": row.name,
                    "globalToken": '<?php echo getToken(300); ?>'
                },
                type: 'post',
                success: function(response) {
                    if (response.error) {
                        avideoAlertError(response.msg);
                    } else {
                        $("#grid").bootgrid('reload');
                    }
                    modal.hidePleaseWait();
                }
            });
        });
        grid.off('click.pluginmgr', '.command-update').on('click.pluginmgr', '.command-update', function(e) {
            var row = grid.DataTable().row($(this).closest('tr')).data();
            $('#inputPluginId').val(row.id);
            $('#inputData').val(JSON.stringify(row.data_object));
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/pluginRunUpdateScript.json.php',
                data: {
                    name: row.name,
                    uuid: row.uuid
                },
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                    $("#grid").bootgrid('reload');
                    avideoResponse(response);
                }
            });
        });

        grid.on("draw.dt", function() {
            try {
                $('[data-toggle="tooltip"], .tooltip').tooltip("hide");
            } catch (error) {

            }
            setTimeout(function() {
                $('[data-toggle="tooltip"]').tooltip({
                    container: 'body',
                    html: true
                });
            }, 500);
            // filters must be re-applied after every draw (sort/reload), not just once after the initial ajax load,
            // otherwise a sort/reload could silently drop the currently active tag filter
            processShow();
            totalVisible();

            if ($(".command-edit[data-pname=WWBNIndex]").length > 0) {
                $(".command-edit[data-pname=WWBNIndex]").remove();
            }
            <?php
            if ($wwbnIndexPlugin) {
            ?>
            <?php
                include("{$global['systemRootPath']}plugin/WWBNIndex/script.js");
            }
            ?>
        });
        $('#savePluginBtn').click(function(evt) {
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'objects/pluginAddDataObject.json.php',
                data: {
                    "id": $('#inputPluginId').val(),
                    "object_data": $('#inputData').val()
                },
                type: 'post',
                success: function(response) {
                    modal.hidePleaseWait();
                    $("#grid").bootgrid('reload');
                    $('#pluginsFormModal').modal('hide');
                }
            });
        });
        $('#upload').click(function(evt) {
            //$('#pluginsImportFormModal').modal();
            avideoModalIframeSmall(webSiteRootURL + 'view/managerPluginUpload.php');
        });
        function getPluginsNeedingUpdate() {
            // paging is off on this grid, so every loaded row is always "current" - read the DataTable
            // directly instead of the bootgrid bridge's page-scoped getCurrentRows() contract.
            var rows = $('#grid').DataTable().rows().data().toArray();
            return rows.filter(function(row) {
                return row.hasOwnProperty('installedPlugin') && row.installedPlugin.hasOwnProperty('pluginversion') && row.installedPlugin.pluginversion != row.pluginversion;
            });
        }
        function updatePluginsSequentially(rows, index, updated, failed) {
            if (index >= rows.length) {
                modal.hidePleaseWait();
                $("#grid").bootgrid('reload');
                if (failed.length) {
                    avideoAlertError(updated.length + " <?php echo __("plugin(s) updated"); ?>, " + failed.length + " <?php echo __("failed"); ?>: " + failed.join(', '));
                } else {
                    avideoToastSuccess(updated.length + " <?php echo __("plugin(s) updated successfully!"); ?>");
                }
                return;
            }
            var row = rows[index];
            modal.setText("<?php echo __("Updating"); ?> " + row.name + " (" + (index + 1) + "/" + rows.length + ")...");
            modal.setProgress(Math.round((index / rows.length) * 100));
            $.ajax({
                url: webSiteRootURL + 'objects/pluginRunUpdateScript.json.php',
                data: {
                    name: row.name,
                    uuid: row.uuid
                },
                type: 'post',
                success: function(response) {
                    if (response && response.error) {
                        failed.push(row.name);
                    } else {
                        updated.push(row.name);
                    }
                },
                error: function() {
                    failed.push(row.name);
                },
                complete: function() {
                    updatePluginsSequentially(rows, index + 1, updated, failed);
                }
            });
        }
        $('#updateAllPluginsBtn').click(async function(evt) {
            var rows = getPluginsNeedingUpdate();
            if (!rows.length) {
                avideoToastSuccess("<?php echo __("All plugins are already up to date!"); ?>");
                return;
            }
            var confirmed = await avideoConfirm(rows.length + " <?php echo __("plugin(s) will be updated. Continue?"); ?>");
            if (!confirmed) {
                return;
            }
            modal.showPleaseWait();
            updatePluginsSequentially(rows, 0, [], []);
        });
        $.ajax({
            url: 'https://streamphp.com/marketplace/plugins.json?jsonp=1',
            dataType: 'jsonp',
            success: function(response) {
                for (i = 0; i < response.rows.length; i++) {
                    var r = response.rows[i];
                    createPluginStoreList('https://youphptube.b-cdn.net/marketplace/' + r.images[0], r.name, r.price, r.description);
                }
            }
        });

    });

    function createPluginStoreList(src, name, price, description) {
        var intPrice = Math.floor(price);
        //var cents = Math.ceil((price - intPrice) * 100);
        var $li = $('#pluginStoreListModel').clone();
        $li.removeClass("hidden").attr("id", "");
        $li.find('.panel-title').text(name);
        $li.find('.int').text(intPrice);
        $li.find('.cents').text("99");
        $li.find('.desc').html(description);
        $li.find('.img').attr("src", src);
        $('#pluginStoreList').append($li);
        // increment the panel count
        panelCount++;

        // if 4 panels have been added, append a clearfix div and reset the counter
        if (panelCount % 4 === 0) {
            $('#pluginStoreList').append('<div class="clearfix"></div>');
        }
    }
</script>
