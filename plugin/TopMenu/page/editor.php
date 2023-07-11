<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';
require_once $global['systemRootPath'] . 'objects/userGroups.php';
require_once $global['systemRootPath'] . 'plugin/TopMenu/Objects/Menu.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugins"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/plugin.php';

$categories = Category::getAllCategories();
array_multisort(array_column($categories, 'hierarchyAndName'), SORT_ASC, $categories);
$groups = UserGroups::getAllUsersGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo getLanguage(); ?>">
    <head>
        <title><?php echo __("Top Menu") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
        <style>
            #sortable li{
                list-style: none;
                margin: 2px;
                padding: 5px;
            }
            #sortable{
                padding: 0;
            }
            .ui-state-highlight{
                height: 30px;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?> ">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-body">

                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#menu">Menu</a></li>
                        <li class="disabled" id="menuItemsTab"><a data-toggle="tab" href="#menuItems" id="menuItemsTabButton">Menu Items</a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="menu" class="tab-pane fade in active">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Menu Form</div>
                                        <div class="panel-body">
                                            <div>
                                                <button class="btn btn-default" id="btnNewMenu"><i class="fa fa-plus"></i> New Menu</button>
                                                <button class="btn btn-success" id="btnSaveMenu"><i class="fa fa-save"></i> Save Menu</button>
                                                <button class="btn btn-primary showWhenHaveId" id="btnEditMenuItens" style="display: none;"><i class="fa fa-edit"></i> Edit Menu Items</button>
                                                <button class="btn btn-danger showWhenHaveId" id="btnDeleteMenu" style="display: none;"><i class="fa fa-times"></i> Delete Menu</button>
                                            </div>
                                            <hr>
                                            <div id="menuForm">
                                                <input type="hidden" id="menuId">
                                                <div class="form-group">
                                                    <label for="menuName">Name:</label>
                                                    <input type="text" class="form-control" id="menuName">
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="menu_order">Order:</label>
                                                        <select class="form-control" id="menu_order">
                                                            <?php
                                                            for ($i = 0; $i < 30; $i++) {
                                                                ?>

                                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status">Status:</label>
                                                        <select class="form-control" id="status">
                                                            <option value="active"><?php echo __('Active'); ?></option>
                                                            <option value="inactive"><?php echo __('Inactive'); ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="type">Type:</label>
                                                        <select class="form-control" id="type">
                                                            <?php
                                                            foreach (Menu::$typeName as $key => $value) {
                                                                echo "<option value=\"{$key}\">{$value}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Icon:</label><br>
                                                        <?php
                                                        echo Layout::getIconsSelect(__("Select an icon for the menu"), "", "menuIcon");
                                                        ?>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="panel  panel-default">
                                        <div class="panel-heading">Menus Available</div>
                                        <div class="panel-body">
                                            <table id="example" class="display table table-striped table-hover" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tfoot>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="menuItems" class="tab-pane fade" >
                            <div class="row">
                                <div class="col-md-8  showWhenHaveId" style="display: none;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Menu Item Form</div>
                                        <div class="panel-body">
                                            <div>
                                                <button class="btn btn-primary" id="btnNewMenuItem"><i class="fa fa-plus"></i> New Menu Item</button>
                                                <button class="btn btn-success" id="btnSaveMenuItem"><i class="fa fa-save"></i> Save Menu Item</button>
                                            </div>
                                            <hr>

                                            <input type="hidden" class="form-control" id="menuItemId">
                                            <div class="form-group">
                                                <label for="title" >Title:</label>
                                                <input type="text" class="form-control" id="title">
                                            </div>
                                            <div class="form-group">
                                                <label for="menuSeoUrlItem">SEO friedly url:</label>
                                                <?php echo $global['webSiteRootURL']; ?>menu/<input type="text" class="form-control" id="menuSeoUrlItem">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="item_order">Order:</label>
                                                    <select class="form-control" id="item_order">
                                                        <?php
                                                        for ($i = 0; $i < 30; $i++) {
                                                            ?>

                                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="item_status">Status:</label>
                                                    <select class="form-control" id="item_status">
                                                        <option value="active"><?php echo __('Active'); ?></option>
                                                        <option value="inactive"><?php echo __('Inactive'); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="pageType">Type:</label>
                                                    <select class="form-control" id="pageType">
                                                        <option value="url"><?php echo __('URL'); ?></option>
                                                        <option value="urlIframe"><?php echo __('URL Iframe'); ?></option>
                                                        <option value="page"><?php echo __('Page'); ?></option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Target:</label><br>
                                                    <select class="form-control" id="target">
                                                        <option value="_self"><?php echo __('_self'); ?></option>
                                                        <option value="_blank"><?php echo __('_blank'); ?></option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Icon Type:</label><br>
                                                    <select class="form-control" id="icon_type">
                                                        <option value="1"><?php echo __('Font Icon'); ?></option>
                                                        <option value="2"><?php echo __('Image Icon'); ?></option>
                                                        <!-- <option value="3"><?php echo __('Upload Icon'); ?></option> -->
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6" id="url-upload-div" style="display:none">
                                                <br>
                                                <div class="form-group" id="url-icon-div" style="display:none">
                                                    <button class="btn btn-primary" id="generate-icon-btn">Generate Icon</button>
                                                </div>
                                                <div class="form-group" id="upload-icon-div" style="display:none">
                                                    <input type="file" id="upload-icon-input">
                                                </div>
                                            </div>

                                            <!-- icon-div -->
                                            <div class="col-md-6" id="menuItemIconDiv">
                                                <div class="form-group">
                                                    <label>Icon:</label><br>
                                                    <div>
                                                        <?php
                                                        echo Layout::getIconsSelect(__("Select an icon for the menu"), "", "menuItemIcon");
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="menuItemIconMobileDiv">
                                                <div class="form-group">
                                                    <label>Icon:</label><br>
                                                    <input type="text" class="form-control" id="menuItemIconMobile">
                                                    Get the icon name from <a href="https://ionicframework.com/docs/v3/ionicons/" target="_blank">here</a>
                                                </div>
                                            </div>

                                            <div id="icon-preview-div" style="display:none">
                                                <div class="col-md-6"></div>
                                                <!-- URL/UPLOAD ICON PREVIEW -->
                                                <div class="col-md-6" id="icon-preview"></div>
                                            </div>

                                            <hr>
                                            <div class="col-md-12">
                                                <div id="divURL" class="divType" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="url">URL:</label>
                                                        <input type="text" class="form-control" id="url">
                                                    </div>
                                                </div>
                                                <div id="divURLIframe" class="divType" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="urlIframe">URL:</label>
                                                        <input type="text" class="form-control" id="urlIframe">
                                                    </div>
                                                </div>
                                                <div id="divText" class="divType"  style="display: none;">
                                                    <div class="form-group">
                                                        <label for="text">text:</label>
                                                        <textarea type="text" class="form-control" id="pageText"></textarea>
                                                        <?php
                                                        echo getTinyMCE("pageText");
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4  showWhenHaveId" style="display: none;">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">Menu Items Order</div>
                                        <div class="panel-body">
                                            <div class="alert alert-warning">Drag and Drop Items to Sort</div>
                                            <ul id="sortable">
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <li class="ui-state-default hidden liModel" itemid="0">
        <button class="btn  btn-default btn-light btn-sm" onclick="editItem(this)">
            <i class="fa fa-edit"></i>
        </button>
        <button class="btn  btn-default btn-light btn-sm" onclick="removeItem(this)">
            <i class="fa fa-trash"></i>
        </button>
        <i class="icon"></i>
        <span>Text</span>
    </li>
    <?php
    include $global['systemRootPath'] . 'view/include/footer.php';
    ?>
    <script type="text/javascript" src="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.js"></script>
    <script>
            var currentItem = [];

            function checkIfHasId() {
                menuId = $('#menuId').val();
                if (!menuId) {
                    $('#menuItemsTab').addClass('disabled');
                    $('.showWhenHaveId').fadeOut();
                } else {
                    $('#menuItemsTab').removeClass('disabled');
                    $('.showWhenHaveId').fadeIn();
                }
            }

            function clearMenuForm() {
                $('#menuId').val("");
                $('#menuName').val("");
                $('#categories_id').val("");
                $('#menu_order').val("");
                $('#position').val("");
                $('#status').val("");
                $('#type').val("");
                $('#users_groups_id').val("");
                $("#menuIcon").val("");
                $("#menuIcon").trigger('change');
                clearMenuItemForm();
                checkIfHasId();
            }

            function clearMenuItemForm() {
                $('#menuItemId').val("");
                $('#title').val("");
                $('#image').val("");
                $('#url').val("");
                $('#class').val("");
                $('#style').val("");
                $('#item_order').val("");
                $('#item_status').val("");
                $('#menuSeoUrlItem').val("");
                $(tinymce.get('pageText').getBody()).html('');
                $("#menuItemIcon").val("");
                $("#menuItemIconMobile").val("");
                $("#menuItemIcon").trigger('change');
                $("#target").val("_self").trigger('change');
                $("#icon_type").val(1).trigger('change');
            }

            function startSortable() {
                $("#sortable").sortable({
                    stop: function (event, ui) {
                        var menu = {};
                        var itens = [];
                        menu.id = $('#menuId').val();
                        $('#sortable li').each(function () {
                            itens.push($(this).attr('itemId'));
                        });
                        menu.itens = itens;
                        console.log(menu);
                        $.ajax({
                            url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuItemSort.json.php',
                            data: {
                                "id": menu.id,
                                "itens": menu.itens
                            },
                            type: 'post',
                            success: function (response) {
                                modal.hidePleaseWait();
                                console.log(response);
                            }
                        });
                    }
                });
                $("#sortable").disableSelection();
            }

            function loadItems(menu_id) {
                clearMenuItemForm();
                $('#sortable').sortable("destroy");
                $.ajax({
                    url: webSiteRootURL+'plugin/TopMenu/menuItems.json.php',
                    data: {
                        "menuId": menu_id
                    },
                    type: 'post',
                    success: function (response) {
                        console.log(response);
                        console.log(response.data.length);
                        currentItem = response.data;
                        $('#sortable').empty();
                        for (i = 0; i < response.data.length; i++) {
                            li = $('.liModel').clone();
                            li.removeClass('liModel');
                            li.removeClass('hidden');
                            // li.find('.icon').addClass(response.data[i].icon);

                            li.attr('id', 'item' + response.data[i].id);
                            li.attr('itemid', response.data[i].id);
                            li.find('span').html(response.data[i].title);
                            $('#sortable').append(li);

                            if (response.data[i].icon_type == 1) {
                                li.find('.icon').css("display", "");
                                li.find('.img_icon').css("display", "none");
                                li.find('.icon').addClass(response.data[i].icon);
                            } else if (response.data[i].icon_type == 2) {
                                li.find('.icon').css("display", "none");
                                li.find('.img_icon').css("display", "");
                                li.find('.img_icon').attr('src', response.data[i].url_icon);
                            }
                        }

                        startSortable();

                    }
                });
            }

            function removeItem(t) {
                id = $(t).parent('li').attr('itemid');
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL+'plugin/TopMenu/menuItemDelete.json.php',
                    data: {
                        "menuItemId": id
                    },
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        console.log(response);
                        clearMenuItemForm();
                        $('#item' + id).fadeOut();
                    }
                });
            }

            function editItem(t) {
                id = $(t).parent('li').attr('itemid');
                item = false;
                for (i = 0; i < currentItem.length; i++) {
                    if (currentItem[i].id == id) {
                        item = currentItem[i];
                        break;
                    }
                }

                loadMenuItemForm(item);
                console.log("loadMenuItemForm", item)
            }


            function loadMenuItemForm(item) {
                clearMenuItemForm();
                if (item !== false) {
                    $('#menuItemId').val(item.id);
                    $('#title').val(item.title);
                    $('#image').val(item.image);
                    $('#url').val(item.url);
                    $('#class').val(item.class);
                    $('#style').val(item.style);
                    $('#item_order').val(item.item_order);
                    $('#item_status').val(item.status);
                    $(tinymce.get('pageText').getBody()).html(item.text);
                    $('#menuSeoUrlItem').val(item.menuSeoUrlItem);
                    $("#menuItemIcon").val(item.icon);
                    $("#menuItemIconMobile").val(item.icon);
                    $("#menuItemIcon").trigger('change');
                    $(tinymce.get('pageText').getBody()).html(item.text);
                    if (item.url.length > 0) {
                        $('#pageType').val('url');
                    } else {
                        $('#pageType').val('page');
                    }
                    expr = /iframe:/;
                    if (expr.test(item.url)) {
                        $('#url').val(item.url.replace("iframe:", ""));
                        $('#pageType').val('urlIframe');
                    }
                    $('#pageType').trigger('change');
                    $("#target").val(item.target).trigger('change');
                    $("#icon_type").val(item.icon_type).trigger('change');
                    if (item.icon_type == 2 && item.url_icon != "") { // URL ICON
                        $("#icon-preview").html('<img src="'+item.url_icon+'">');
                    }
                }
            }

            $(document).ready(function () {

                $(".nav-tabs a[data-toggle=tab]").on("click", function (e) {
                    if ($(this).parent().hasClass("disabled")) {
                        e.preventDefault();
                        return false;
                    }
                });

                $('#pageType').change(function () {
                    console.log($(this).val());
                    if ($(this).val() == 'url' || $(this).val() == 'urlIframe') {
                        $('#divText').slideUp();
                        $('#divURL').slideDown();
                    } else {
                        $('#divText').slideDown();
                        $('#divURL').slideUp();
                    }
                });

                $('#pageType').trigger('change');

                $("#icon_type").on("change", function() {
                    var icon_type = $(this).val();
                    if (icon_type == 1) { // ICON
                        $("#url-upload-div").css("display", "none");
                        $("#menuItemIconDiv").css("display", "");
                        $("#url-icon-div").css("display", "none");
                        $("#upload-icon-div").css("display", "none");
                        $("#icon-preview-div").css("display", "none");
                        $("#icon-preview").html("");
                    } else if (icon_type == 2) { // URL ICON
                        var type = $("#pageType").val();
                        if (type == "page") {
                            $("#icon_type").val(1).trigger('change');
                            swal("Notice", "Image Icon is not applicable with the selected type.", "info");
                        } else {
                            $("#url-upload-div").css("display", "");
                            $("#menuItemIconDiv").css("display", "none");
                            $("#url-icon-div").css("display", "");
                            $("#upload-icon-div").css("display", "none");
                            $("#icon-preview-div").css("display", "");
                            $("#icon-preview").html("");
                        }
                    } else if (icon_type == 3) { // UPLOAD ICON
                        $("#url-upload-div").css("display", "");
                        $("#menuItemIconDiv").css("display", "none");
                        $("#url-icon-div").css("display", "none");
                        $("#upload-icon-div").css("display", "");
                        $("#icon-preview-div").css("display", "");
                        $("#icon-preview").html("");
                    }
                });

                $("#generate-icon-btn").on("click", function() {
                    var icon_type = $("#icon_type").val();
                    var url = $("#url").val();
                    var urlIframe = $("#urlIframe").val();
                    var generate_url = "";
                    if (icon_type == 2) {
                        if (url == "") {
                            swal("Required Field", "URL is required!", "warning");
                            return false;
                        } else {
                            generate_url = url;
                        }
                    } else if (icon_type == 3) {
                        if (urlIframe == "") {
                            swal("Required Field", "URL is required!", "warning");
                            return false;
                        } else {
                            generate_url = urlIframe;
                        }
                    }
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuItemGenerateUrlIcon.json.php',
                        data: {url:generate_url},
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            if (response) {
                                $("#icon-preview").html('<img src="'+response+'videos/favicon.png"><br>');
                            } else {
                                swal("Error", "URL must be an avideo platform. Also make sure the platform is accessible and not restricted.", "error");
                                $("#icon-preview").html("");
                            }
                        },
                        error: function(e) {
                            console.log(e)
                        }
                    });
                    // image preview
                });

                $("#upload-icon-input").on("change", function(e) {
                    // image preview
                    $("#icon-preview").html();
                });

                var table = $('#example').DataTable({
                    "ajax": webSiteRootURL+"plugin/TopMenu/menus.json.php",
                    "columns": [
                        {"data": "menuName"},
                        {"data": "status"}
                    ],
                    select: true,

                });

                $('#btnNewMenu').click(function () {
                    clearMenuForm();
                });
                $('#btnNewMenuItem').click(function () {
                    clearMenuItemForm();
                });

                $('#example tbody').on('click', 'tr', function () {
                    var data = table.row(this).data();
                    console.log(data);
                    $('#menuId').val(data.id);
                    $('#menuName').val(data.menuName);
                    $('#categories_id').val(data.categories_id);
                    $('#menu_order').val(data.menu_order);
                    $('#position').val(data.position);
                    $('#status').val(data.status);
                    $('#type').val(data.type);
                    $('#users_groups_id').val(data.users_groups_id);
                    $("#menuIcon").val(data.icon);
                    $("#menuIcon").trigger('change');
                    if(data.type == 8){
                       $("#menuItemIconDiv").hide();
                       $("#menuItemIconMobileDiv").show();
                    }else{
                       $("#menuItemIconDiv").show();
                       $("#menuItemIconMobileDiv").hide();
                    }
                    checkIfHasId();
                    loadItems(data.id);
                });

                startSortable();
                $("#sortable").disableSelection();
                $('#btnSaveMenu').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuSave.json.php',
                        data: {
                            "menuId": $('#menuId').val(),
                            "menuName": $('#menuName').val(),
                            "categories_id": $('#categories_id').val(),
                            "menu_order": $('#menu_order').val(),
                            "position": $('#position').val(),
                            "status": $('#status').val(),
                            "type": $('#type').val(),
                            "users_groups_id": $('#users_groups_id').val(),
                            "icon": $("#menuIcon").val()
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            console.log(response);
                            table.ajax.reload();
                            clearMenuForm();
                        }
                    });
                });
                $('#btnSaveMenuItem').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuItemSave.json.php',
                        data: {
                            "menuItemId": $('#menuItemId').val(),
                            "menuId": $('#menuId').val(),
                            "title": $('#title').val(),
                            "image": $('#image').val(),
                            "url": $('#pageType').val() == 'url' ? $('#url').val() : ($('#pageType').val() == 'urlIframe' ? ("iframe:" + $('#url').val()) : ''),
                            "menuSeoUrlItem": $("#menuSeoUrlItem").val(),
                            "class": $('#class').val(),
                            "style": $('#style').val(),
                            "item_order": $('#item_order').val(),
                            "item_status": $('#item_status').val(),
                            "text": $('#pageType').val() == 'page' ? $(tinymce.get('pageText').getBody()).html() : '',
                            "icon": $("#menuItemIcon").val(),
                            "mobileicon": $("#menuItemIconMobile").val(),
                            "target": $("#target").val(),
                            "icon_type": $("#icon_type").val(),
                            "url_icon": ($("#icon_type").val() == 2) ? ($("#icon-preview").find('img').length > 0 ? $("#icon-preview").find('img').attr('src') : '') : '',
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            console.log(response);
                            loadItems($('#menuId').val());
                        }
                    });
                });
                $('#btnDeleteMenu').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuDelete.json.php',
                        data: {
                            "menuId": $('#menuId').val()
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                            console.log(response);
                            table.ajax.reload();
                            clearMenuForm();
                        }
                    });
                });
                $('#btnEditMenuItens').click(function () {
                    $('#menuItemsTabButton').tab('show');
                });

            });
    </script>
</body>
</html>
