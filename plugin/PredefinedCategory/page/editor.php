<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();
array_multisort(array_column($categories, 'hierarchyAndName'), SORT_ASC, $categories);
$groups = UserGroups::getAllUsersGroups();
//$users = User::getAllUsers();
$o = AVideoPlugin::getObjectData("PredefinedCategory");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Predefined Category") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .funkyradio-info label, .funkyradio-default label, .funkyradio-warning label, .funkyradio-danger label{
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .funkyradio div {
                clear: both;
                overflow: hidden;
            }

            .funkyradio label {
                width: 100%;
                border-radius: 3px;
                border: 1px solid #D1D3D4;
                font-weight: normal;
            }

            .funkyradio input[type="radio"]:empty,
            .funkyradio input[type="checkbox"]:empty {
                display: none;
            }

            .funkyradio input[type="radio"]:empty ~ label,
            .funkyradio input[type="checkbox"]:empty ~ label {
                position: relative;
                line-height: 2.5em;
                text-indent: 3.25em;
                cursor: pointer;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }

            .funkyradio input[type="radio"]:empty ~ label:before,
            .funkyradio input[type="checkbox"]:empty ~ label:before {
                position: absolute;
                display: block;
                top: 0;
                bottom: 0;
                left: 0;
                content: '';
                width: 2.5em;
                background: #D1D3D4;
                border-radius: 3px 0 0 3px;
            }

            .funkyradio input[type="radio"]:hover:not(:checked) ~ label,
            .funkyradio input[type="checkbox"]:hover:not(:checked) ~ label {
                color: #888;
            }

            .funkyradio input[type="radio"]:hover:not(:checked) ~ label:before,
            .funkyradio input[type="checkbox"]:hover:not(:checked) ~ label:before {
                content: '\2714';
                text-indent: .9em;
                color: #C2C2C2;
            }

            .funkyradio input[type="radio"]:checked ~ label,
            .funkyradio input[type="checkbox"]:checked ~ label {
                color: #777;
            }

            .funkyradio input[type="radio"]:checked ~ label:before,
            .funkyradio input[type="checkbox"]:checked ~ label:before {
                content: '\2714';
                text-indent: .9em;
                color: #333;
                background-color: #ccc;
            }

            .funkyradio input[type="radio"]:focus ~ label:before,
            .funkyradio input[type="checkbox"]:focus ~ label:before {
                box-shadow: 0 0 0 3px #999;
            }

            .funkyradio-default input[type="radio"]:checked ~ label:before,
            .funkyradio-default input[type="checkbox"]:checked ~ label:before {
                color: #333;
                background-color: #ccc;
            }

            .funkyradio-primary input[type="radio"]:checked ~ label:before,
            .funkyradio-primary input[type="checkbox"]:checked ~ label:before {
                color: #fff;
                background-color: #337ab7;
            }

            .funkyradio-success input[type="radio"]:checked ~ label:before,
            .funkyradio-success input[type="checkbox"]:checked ~ label:before {
                color: #fff;
                background-color: #5cb85c;
            }

            .funkyradio-danger input[type="radio"]:checked ~ label:before,
            .funkyradio-danger input[type="checkbox"]:checked ~ label:before {
                color: #fff;
                background-color: #d9534f;
            }

            .funkyradio-warning input[type="radio"]:checked ~ label:before,
            .funkyradio-warning input[type="checkbox"]:checked ~ label:before {
                color: #fff;
                background-color: #f0ad4e;
            }

            .funkyradio-info input[type="radio"]:checked ~ label:before,
            .funkyradio-info input[type="checkbox"]:checked ~ label:before {
                color: #fff;
                background-color: #5bc0de;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="col-md-3">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Here you can choose the default category whenever a video is submitted to your site.
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Site Default Category</div>
                    <div class="panel-body">
                        <div class="funkyradio">
                            <?php
                            foreach ($categories as $value) {
                                ?>
                                <div class="funkyradio-primary">
                                    <input type="radio" name="radio" class="categoryRadio" id="radio<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>" <?php if ($o->defaultCategory == $value['id']) { ?>checked<?php } ?>/>
                                    <label for="radio<?php echo $value['id']; ?>"><i class="<?php echo $value['iconClass']; ?>"  style="display: unset;"></i> <?php echo $value['name']; ?></label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">Default Category per User</div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Select a user then choose the defaul category.
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Users <input id="searchUser" type="text" class="form-control" placeholder="<?php echo __('Search User'); ?>" /></div>
                                <div class="panel-body">
                                    <div class="funkyradio" id="funkyradiousers"></div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Category</div>
                                <div class="panel-body">
                                    <div class="funkyradio">
                                        <div class="funkyradio-default">
                                            <input type="radio" name="radioUserCat" class="categoryGroupRadio" id="radioUserCat" value="0" checked="checked"/>
                                            <label for="radioUserCat">Use Site Default Category</label>
                                        </div>
                                        <?php
                                        foreach ($categories as $value) {
                                            ?>
                                            <div class="funkyradio-info">
                                                <input type="radio" name="radioUserCat" class="categoryGroupRadio" id="radioUserCat<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>"/>
                                                <label for="radioUserCat<?php echo $value['id']; ?>"><i class="<?php echo $value['iconClass']; ?>" style="display: unset;"></i> <?php echo $value['name']; ?></label>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Here you can choose the default user group.<br>
                    When ever you upload a new video this video will be exclusive for the 
                    <a href="http://127.0.0.1/AVideo/usersGroups">user groups</a> you selected.<br>
                    Leave it blank to be public by default
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Site Default User Group</div>
                    <div class="panel-body">
                        <div class="funkyradio">
                            <?php
                            foreach ($groups as $value) {
                                ?>
                                <div class="funkyradio-danger">
                                    <input type="checkbox" name="groupRadio" class="groupRadio" id="groupRadio<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>" <?php if (!empty($o->{"AddVideoOnGroup_[{$value['id']}]_"})) { ?>checked<?php } ?>/>
                                    <label for="groupRadio<?php echo $value['id']; ?>"> <?php echo $value['group_name']; ?></label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            var userCategory = <?php echo json_encode($o->userCategory); ?>;
            var userSearchTimeout;
            $(document).ready(function () {
                $('.groupRadio').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'editorSave.php',
                        data: {
                            "groupRadio": $(this).is(":checked"),
                            "groupRadioValue": $(this).val()
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });
                $('.categoryRadio').click(function () {
                    modal.showPleaseWait();
                    $.ajax({
                        url: 'editorSave.php',
                        data: {
                            "category_id": $(this).val()
                        },
                        type: 'post',
                        success: function (response) {
                            modal.hidePleaseWait();
                        }
                    });
                });

                searchUserForCat('');

                $("input[name='radioUser']:checked").trigger('click')

                $('.categoryGroupRadio').click(function () {
                    modal.showPleaseWait();
                    var radioValue = $("input[name='radioUser']:checked").val();
                    $.ajax({
                        url: 'editorSave.php',
                        data: {
                            "user_id": radioValue,
                            "category_id": $(this).val()
                        },
                        type: 'post',
                        success: function (response) {
                            userCategory = response.userCategory;
                            modal.hidePleaseWait();
                        }
                    });
                });

                $('#searchUser').keyup(function () {
                    clearTimeout(userSearchTimeout);
                    userSearchTimeout = setTimeout(function () {
                        searchUserForCat($('#searchUser').val());
                    }, 500);
                });
            });

            function searchUserForCat(search) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'objects/users.json.php?status=a&rowCount=10&searchPhrase=' + search,
                    success: function (response) {
                        console.log(response);
                        $('#funkyradiousers').empty();
                        for (var index in response.rows) {
                            var user = response.rows[index];
                            if (typeof user == 'function') {
                                continue;
                            }
                            var html = '<div class="funkyradio-warning">';
                            html += '<input type="radio" name="radioUser" class="userRadio" id="radioUser' + user.id + '" value="' + user.id + '"/>';
                            html += '<label for="radioUser' + user.id + '"> ' + user.identification + '</label>';
                            html += '</div>'
                            $('#funkyradiousers').append(html);
                        }
                        setOnClickUser();
                        modal.hidePleaseWait();
                    }
                });
            }

            function setOnClickUser() {
                $('.userRadio').click(function () {
                    var value = 0;
                    if (typeof userCategory != 'undefined' && userCategory[$(this).val()]) {
                        value = userCategory[$(this).val()];
                    }
                    $("input[name=radioUserCat][value=0]").prop('checked', true);
                    $('[name=radioUserCat]').removeAttr('checked');
                    $("input[name=radioUserCat][value=" + value + "]").prop('checked', true);
                });
            }
        </script>
    </body>
</html>
