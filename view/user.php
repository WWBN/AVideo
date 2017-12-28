<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';

$tags = User::getTags(User::getId());
$tagsStr = "";
foreach ($tags as $value) {
    $tagsStr .= "<span class=\"label label-{$value->type} fix-width\">{$value->text}</span>";
}
$json_file = file_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
$advancedCustom = json_decode($json_file);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("User"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link href="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.css" rel="stylesheet" type="text/css"/>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/Croppie/croppie.min.js" type="text/javascript"></script>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container-fluid">
            <?php
            if (User::isLogged()) {
                $user = new User("");
                $user->loadSelfUser();
                ?>
                <div class="row">
                    <div>
                        <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                            <?php echo $tagsStr; ?>
                            <fieldset>
                                <legend>
                                    <?php echo __("Update your user") ?>

                                </legend>
                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("Name"); ?></label>
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                            <input  id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control"  type="text" value="<?php echo $user->getName(); ?>" required >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                            <input  id="inputUser" placeholder="<?php echo __("User"); ?>" class="form-control"  type="text" value="<?php echo $user->getUser(); ?>" required >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                            <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email" value="<?php echo $user->getEmail(); ?>" required >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("New Password"); ?></label>
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                            <input  id="inputPassword" placeholder="<?php echo __("New Password"); ?>" class="form-control"  type="password" value="" >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label"><?php echo __("Confirm New Password"); ?></label>
                                    <div class="col-md-8 inputGroupContainer">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                            <input  id="inputPasswordConfirm" placeholder="<?php echo __("Confirm New Password"); ?>" class="form-control"  type="password" value="" >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-12 ">
                                        <div id="croppie"></div>
                                        <a id="upload-btn" class="btn btn-primary btn-xs btn-block"><?php echo __("Upload a Photo"); ?></a>
                                    </div>
                                    <input type="file" id="upload" value="Choose a file" accept="image/*" style="display: none;" />
                                </div>

                                <div class="form-group">
                                    <div class="col-md-12 ">
                                        <div id="croppieBg"></div>
                                        <a id="upload-btnBg" class="btn btn-success btn-xs btn-block"><?php echo __("Upload a Background"); ?></a>
                                    </div>
                                    <input type="file" id="uploadBg" value="Choose a file" accept="image/*" style="display: none;" />
                                </div>


                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label"></label>
                                    <div class="col-md-8">
                                        <button type="submit" class="btn btn-primary" ><?php echo __("Save"); ?> <span class="glyphicon glyphicon-save"></span></button>
                                    </div>
                                </div>
                            </fieldset>
                        </form>

                    </div>
                </div>
                <script>
                    var uploadCrop;
                    function readFile(input, crop) {
                        console.log(input);
                        console.log($(input)[0]);
                        console.log($(input)[0].files);
                        if ($(input)[0].files && $(input)[0].files[0]) {
                            var reader = new FileReader();

                            reader.onload = function (e) {
                                crop.croppie('bind', {
                                    url: e.target.result
                                }).then(function () {
                                    console.log('jQuery bind complete');
                                });

                            }

                            reader.readAsDataURL($(input)[0].files[0]);
                        } else {
                            swal("Sorry - you're browser doesn't support the FileReader API");
                        }
                    }
                    $(document).ready(function () {
                        $('#upload').on('change', function () {
                            readFile(this, uploadCrop);
                        });
                        $('#upload-btn').on('click', function (ev) {
                            $('#upload').trigger("click");
                        });
                        $('#uploadBg').on('change', function () {
                            readFile(this, uploadCropBg);
                        });
                        $('#upload-btnBg').on('click', function (ev) {
                            $('#uploadBg').trigger("click");
                        });

                        uploadCrop = $('#croppie').croppie({
                            url: '<?php echo $user->getPhoto(); ?>',
                            enableExif: true,
                            viewport: {
                                width: 150,
                                height: 150
                            },
                            boundary: {
                                width: 300,
                                height: 300
                            }
                        });

                        uploadCropBg = $('#croppieBg').croppie({
                            url: '<?php echo $user->getBackgroundURL(); ?>',
                            enableExif: true,
                            viewport: {
                                width: 1250,
                                height: 250
                            },
                            boundary: {
                                width: 1300,
                                height: 300
                            }
                        });
                        $('#updateUserForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            var pass1 = $('#inputPassword').val();
                            var pass2 = $('#inputPasswordConfirm').val();
                            // password dont match
                            if (pass1 != '' && pass1 != pass2) {
                                modal.hidePleaseWait();
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your password does not match!"); ?>", "error");
                                return false;
                            } else {
                                $.ajax({
                                    url: 'updateUser',
                                    data: {"user": $('#inputUser').val(), "pass": $('#inputPassword').val(), "email": $('#inputEmail').val(), "name": $('#inputName').val()},
                                    type: 'post',
                                    success: function (response) {
                                        if (response.status > "0") {
                                            uploadCrop.croppie('result', {
                                                type: 'canvas',
                                                size: 'viewport'
                                            }).then(function (resp) {
                                                $.ajax({
                                                    type: "POST",
                                                    url: "savePhoto",
                                                    data: {
                                                        imgBase64: resp
                                                    }
                                                }).done(function (o) {
                                                    uploadCropBg.croppie('result', {
                                                        type: 'canvas',
                                                        size: 'viewport'
                                                    }).then(function (resp) {
                                                        $.ajax({
                                                            type: "POST",
                                                            url: "saveBackground",
                                                            data: {
                                                                imgBase64: resp
                                                            }
                                                        }).done(function (o) {
                                                            modal.hidePleaseWait();
                                                        });
                                                    });
                                                });
                                            });
                                        } else {
                                            swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");
                                            modal.hidePleaseWait();
                                        }
                                    }
                                });
                                return false;
                            }
                        });
                    });
                </script>
                <?php
            } else {
                ?>
                <div class="row">
                    <div class="col-xs-1 col-sm-2 col-lg-4"></div>
                    <div class="col-xs-10 col-sm-8 col-lg-4">
                        <form class="form-compact well form-horizontal"  id="loginForm">
                            <fieldset>
                                <legend><?php echo __("Please sign in"); ?></legend>


                                <?php
                                if (empty($advancedCustom->disableNativeSignIn)) {
                                    ?>
                                    <div class="form-group">
                                        <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                                <input  id="inputUser" placeholder="<?php echo __("User"); ?>" class="form-control"  type="text" value="" required >
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-md-4 control-label"><?php echo __("Password"); ?></label>
                                        <div class="col-md-8 inputGroupContainer">
                                            <div class="input-group">
                                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                                <input  id="inputPassword" placeholder="<?php echo __("Password"); ?>" class="form-control"  type="password" value="" >
                                            </div>
                                            <?php
                                            if (empty($advancedCustom->disableNativeSignUp)) {
                                                ?>
                                                <small><a href="#" id="forgotPassword"><?php echo __("I forgot my password"); ?></a></small>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <!-- Button -->
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-success  btn-block" id="mainButton" ><span class="fa fa-sign-in"></span> <?php echo __("Sign in"); ?></button>
                                        </div>
                                    </div>
                                    <?php
                                    if (empty($advancedCustom->disableNativeSignUp)) {
                                        ?>
                                        <div class="form-group">
                                            <div class="col-md-12">
                                                <a href="signUp" class="btn btn-primary btn-block"  id="facebookButton"><span class="fa fa-user-plus"></span> <?php echo __("Sign up"); ?></a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                                <div class="form-group">
                                    <?php
                                    $login = YouPHPTubePlugin::getLogin();
                                    foreach ($login as $value) {
                                        ?>
                                        <div class="col-md-6">
                                            <a href="login?type=<?php echo $value['parameters']->type; ?>" class="<?php echo $value['parameters']->class; ?>" ><span class="<?php echo $value['parameters']->icon; ?>"></span> <?php echo $value['parameters']->type; ?></a>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </fieldset>

                        </form>

                    </div>
                    <div class="col-xs-1 col-sm-2 col-lg-4"></div>
                </div>
                <script>
                    $(document).ready(function () {
                        <?php
                        if(!empty($_GET['error'])){
                            ?>
                            swal("<?php echo __("Sorry!"); ?>", "<?php echo addslashes($_GET['error']); ?>", "error");    
                            <?php
                        }
                        ?>
                        $('#loginForm').submit(function (evt) {
                            evt.preventDefault();
                            modal.showPleaseWait();
                            $.ajax({
                                url: 'login',
                                data: {"user": $('#inputUser').val(), "pass": $('#inputPassword').val()},
                                type: 'post',
                                success: function (response) {
                                    if (!response.isLogged) {
                                        modal.hidePleaseWait();
                                        swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user or password is wrong!"); ?>", "error");
                                    } else {
                                        document.location = '<?php echo!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : $global['webSiteRootURL']; ?>'
                                    }
                                }
                            });
                        });
                        $('#forgotPassword').click(function () {
                            var user = $('#inputUser').val();
                            if (!user) {
                                swal("<?php echo __("Sorry!"); ?>", "<?php echo __("You need to inform what is your user!"); ?>", "error");
                                return false;
                            }
                            var capcha = '<span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha" id="captcha"></span><span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span><input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">';
                            swal({
                                title: user + ", <?php echo __("Are you sure?"); ?>",
                                text: "<?php echo __("We will send you a link, to your e-mail, to recover your password!"); ?>" + capcha,
                                type: "warning",
                                html: true,
                                showCancelButton: true,
                                confirmButtonClass: "btn-danger",
                                confirmButtonText: "Yes, send it!",
                                closeOnConfirm: false
                            },
                                    function () {
                                        modal.showPleaseWait();
                                        $.ajax({
                                            url: 'recoverPass',
                                            data: {"user": $('#inputUser').val(), "captcha": $('#captchaText').val()},
                                            type: 'post',
                                            success: function (response) {
                                                if (response.error) {
                                                    swal("<?php echo __("Error"); ?>", response.error, "error");
                                                } else {
                                                    swal("<?php echo __("E-mail sent"); ?>", "<?php echo __("We sent you an e-mail with instructions"); ?>", "success");
                                                }
                                                modal.hidePleaseWait();
                                            }
                                        });

                                    });

                            $('#btnReloadCapcha').click(function () {
                                $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?' + Math.random());
                                $('#captchaText').val('');
                            });
                        });
                    }
                    );

                </script>
                <?php
            }
            ?>

        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
