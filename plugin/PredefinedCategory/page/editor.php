<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/category.php';
$categories = Category::getAllCategories();
$groups = UserGroups::getAllUsersGroups();
$users = User::getAllUsers();
$o = YouPHPTubePlugin::getObjectData("PredefinedCategory");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Predefined Category</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>

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
                <div class="card">
                    <div class="card-header">Site Default Category</div>
                    <div class="card-body">
                        <div class="funkyradio">
                            <?php
                            foreach ($categories as $value) {
                                ?>
                                <div class="funkyradio-primary">
                                    <input type="radio" name="radio" class="categoryRadio" id="radio<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>" <?php if ($o->defaultCategory == $value['id']) { ?>checked<?php } ?>/>
                                    <label for="radio<?php echo $value['id']; ?>"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['name']; ?></label>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Default Category per User</div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> Select a user then choose the defaul category.
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Users</div>
                                <div class="card-body">
                                    <div class="funkyradio">
                                        <?php
                                        $count = 0;
                                        foreach ($users as $value) {
                                            ?>
                                            <div class="funkyradio-warning">
                                                <input type="radio" name="radioUser" class="userRadio" id="radioUser<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>" <?php if (empty($count)) { ?>checked<?php } ?>/>
                                                <label for="radioUser<?php echo $value['id']; ?>"> <?php echo $value['user']; ?></label>
                                            </div>
                                            <?php
                                            $count++;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">Category</div>
                                <div class="card-body">
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
                                                <label for="radioUserCat<?php echo $value['id']; ?>"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo $value['name']; ?></label>
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
                    <a href="http://127.0.0.1/YouPHPTube/usersGroups">user groups</a> you selected.<br>
                    Leave it blank to be public by default
                </div>
                <div class="card">
                    <div class="card-header">Site Default User Group</div>
                    <div class="card-body">
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
                                
                $('.userRadio').click(function () {
                    var value = 0;
                    if(typeof userCategory != 'undefined' && userCategory[$(this).val()]){
                        value = userCategory[$(this).val()];
                    }
                    $("input[name=radioUserCat][value=0]").prop('checked', true);
                    $('[name=radioUserCat]').removeAttr('checked');
                    $("input[name=radioUserCat][value=" + value + "]").prop('checked', true);
                });
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
            });
        </script>
    </body>
</html>
