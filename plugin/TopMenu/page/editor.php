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
$groups = UserGroups::getAllUsersGroups();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Top Menu</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/css/fontawesome-iconpicker.min.css" rel="stylesheet" type="text/css"/>
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
            <div class="bgWhite bg-light clear clearfix">

                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" href="#menu">Menu</a></li>
                    <li class="disabled" id="menuItemsTab"><a data-toggle="tab" href="#menuItems" id="menuItemsTabButton">Menu Items</a></li>
                </ul>
                <div class="tab-content">
                    <div id="menu" class="tab-pane fade in active">
                        <div class="col-md-8">

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
                                        <!--
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label for="categories_id">Category:</label>
                                                <select class="form-control" id="categories_id">
                                                    <option value="0">All Categories</option>
                                        <?php
                                        foreach ($categories as $key => $value) {
                                            ?>

                                                                                <option value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
                                            <?php
                                        }
                                        ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="users_groups_id">Group:</label>
                                                <select class="form-control" id="users_groups_id">
                                                    <option value="0">All Groups</option>
                                        <?php
                                        foreach ($groups as $key => $value) {
                                            ?>

                                                                                <option value="<?php echo $value['id']; ?>"><?php echo $value['group_name']; ?></option>
                                            <?php
                                        }
                                        ?>
                                                </select>
                                            </div>
                                        </div>
                                        -->
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
                                        <!--
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="position">Position:</label>
                                                <select class="form-control" id="position">
                                                    <option value="left"><?php echo __('Left'); ?></option>
                                                    <option value="right"><?php echo __('Right'); ?></option>
                                                    <option value="center"><?php echo __('Center'); ?></option>
                                                    <option value="bottom"><?php echo __('Bottom'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        -->
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="type">Type:</label>
                                                <select class="form-control" id="type">
                                                    <option value="1"><?php echo __('Default'); ?></option>
                                                    <option value="2"><?php echo __('Left Menu'); ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Icon:</label><br>
                                                <div class="btn-group">
                                                    <button data-selected="graduation-cap" type="button" class="icp iconMenu btn  btn-default btn-light  dropdown-toggle iconpicker-component" data-toggle="dropdown">
                                                        <?php echo __("Select an icon for the menu"); ?>  <i class="fa fa-fw"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <div class="dropdown-menu"></div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card ">
                                <div class="card-header">Menus Available</div>
                                <div class="card-body">
                                    <table id="example" class="display" width="100%" cellspacing="0">
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

                    <div id="menuItems" class="tab-pane fade" >
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
                                            <?=$global['webSiteRootURL'];?>menu/<input type="text" class="form-control" id="menuSeoUrlItem">
                                        </div>                                    <!--
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Image:</label>
                                            <input type="text" class="form-control" id="image">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="class">class:</label>
                                            <input type="text" class="form-control" id="class">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="style">style:</label>
                                            <input type="text" class="form-control" id="style">
                                        </div>
                                    </div>
                                    -->
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
                                            <label>Icon:</label><br>
                                            <div class="btn-group">
                                                <button data-selected="graduation-cap" type="button" class="icp iconMenuItem btn  btn-default btn-light dropdown-toggle iconpicker-component" data-toggle="dropdown">
                                                    <?php echo __("Select an icon for the menu"); ?>  <i class="fa fa-fw"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <div class="dropdown-menu"></div>
                                            </div>
                                        </div>
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
                                                <textarea type="text" class="form-control" id="text"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4  showWhenHaveId" style="display: none;">
                            <div class="card">
                                <div class="card-header">Menu Items Order</div>
                                <div class="card-body">
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
    <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
    <script src="<?php echo $global['webSiteRootURL']; ?>js/jquery-ui/jquery-ui.min.js" type="text/javascript"></script>
    <script src="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.min.js" type="text/javascript"></script>
    <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap3-wysiwyg/bootstrap3-wysihtml5.all.js" type="text/javascript"></script>  
    <script src="<?php echo $global['webSiteRootURL']; ?>css/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.min.js" type="text/javascript"></script>
    <script>
            var currentItem = [];

            function checkIfHasId(){
                menuId = $('#menuId').val();
                if(!menuId){
                    $('#menuItemsTab').addClass('disabled');
                    $('.showWhenHaveId').fadeOut();
                }else{
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
                $('iframe').contents().find('.wysihtml5-editor').html('');
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
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuItems.json.php',
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
                            li.find('.icon').addClass(response.data[i].icon);

                            li.attr('id', 'item' + response.data[i].id);
                            li.attr('itemid', response.data[i].id);
                            li.find('span').html(response.data[i].title);
                            $('#sortable').append(li);
                        }

                        startSortable();

                    }
                });
            }

            function removeItem(t) {
                id = $(t).parent('li').attr('itemid');
                modal.showPleaseWait();
                $.ajax({
                    url: '<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menuItemDelete.json.php',
                    data: {
                        "menuItemId": id
                    },
                    type: 'post',
                    success: function (response) {
                        modal.hidePleaseWait();
                        console.log(response);
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
                    $('#text').val(item.text);
                    $('#menuSeoUrlItem').val(item.menuSeoUrlItem);
                    $(".iconMenuItem i").attr("class", item.icon);
                    $('iframe').contents().find('.wysihtml5-editor').html(item.text);
                    if (item.url.length > 0) {
                        $('#pageType').val('url');
                    } else {
                        $('#pageType').val('page');
                    }
                    expr = /iframe:/;
                    if(expr.test(item.url)){
                        $('#url').val(item.url.replace("iframe:", ""));
                        $('#pageType').val('urlIframe');
                    }
                    $('#pageType').trigger('change');
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

                $('.iconMenu, .iconMenuItem').iconpicker({});

                var table = $('#example').DataTable({
                    "ajax": "<?php echo $global['webSiteRootURL']; ?>plugin/TopMenu/menus.json.php",
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

                $('#text').wysihtml5({toolbar: {
                        "html": true,
                        "color": true
                    }
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
                    $(".iconMenu i").attr("class", data.icon)
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
                            "icon": $(".iconMenu i").hasClass("iconpicker-component") ? "" : $(".iconMenu i").attr("class")
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
                            "url": $('#pageType').val() == 'url' ? $('#url').val() : ($('#pageType').val() == 'urlIframe' ? ("iframe:"+$('#url').val()) : ''),
                            "menuSeoUrlItem": $("#menuSeoUrlItem").val(),
                            "class": $('#class').val(),
                            "style": $('#style').val(),
                            "item_order": $('#item_order').val(),
                            "item_status": $('#item_status').val(),
                            "text": $('#pageType').val() == 'page' ? $('#text').val() : '',
                            "icon": $(".iconMenuItem i").hasClass("iconpicker-component") ? "" : $(".iconMenuItem i").attr("class")
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
