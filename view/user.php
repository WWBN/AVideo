<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/configuration.php';
$config = new Configuration();
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?> :: <?php echo __("User"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body>
        <?php
        include 'include/navbar.php';
        ?>

        <div class="container">
            <?php
            if (User::isLogged()) {
                $user = new User("");
                $user->loadSelfUser();
                ?>
                <div class="row">
                    <div class="col-xs-6 col-sm-4 col-lg-3"></div>
                    <div class="col-xs-6 col-sm-4 col-lg-6">
                        <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                            <fieldset>
                                <legend><?php echo __("Update your user"); ?></legend>
                                
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
                    <div class="col-xs-6 col-sm-4 col-lg-3"></div>
                </div>
                <script>
                    $(document).ready(function () {
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
                                        if (response.status === "1") {
                                            swal("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your user has been updated!"); ?>", "success");
                                        } else {
                                            swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been updated!"); ?>", "error");
                                        }
                                        modal.hidePleaseWait();
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
                    <div class="col-xs-6 col-sm-4"></div>
                    <div class="col-xs-6 col-sm-4">
                        <form class="form-compact well form-horizontal"  id="loginForm">
                            <fieldset>
                                <legend><?php echo __("Please sign in"); ?></legend>
                            <label for="inputEmail" class="sr-only"><?php echo __("User"); ?></label>
                            <input type="text" id="inputUser" class="form-control first" placeholder="<?php echo __("User"); ?>" required autofocus>
                            <label for="inputPassword" class="sr-only"><?php echo __("Password"); ?></label>
                            <input type="password" id="inputPassword" class="form-control last" placeholder="<?php echo __("Password"); ?>" required>
                            <button class="btn btn-lg btn-primary btn-block"><?php echo __("Sign in"); ?></button>
                            </fieldset>
                        </form>

                    </div>
                    <div class="col-xs-6 col-sm-4"></div>
                </div>
                <script>
                    $(document).ready(function () {
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
                                        document.location = '<?php echo $global['webSiteRootURL']; ?>'
                                    }
                                }
                            });
                        });
                    });

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
