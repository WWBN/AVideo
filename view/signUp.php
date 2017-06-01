<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
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


            <div class="row">
                <div class="col-xs-1 col-sm-1 col-lg-2"></div>
                <div class="col-xs-10 col-sm-10 col-lg-8">
                    <form class="form-compact well form-horizontal"  id="updateUserForm" onsubmit="">
                        <fieldset>
                            <legend><?php echo __("Sign Up"); ?></legend>

                            <div class="form-group">
                                <label class="col-md-4 control-label"><?php echo __("Name"); ?></label>  
                                <div class="col-md-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                        <input  id="inputName" placeholder="<?php echo __("Name"); ?>" class="form-control"  type="text" value="" required >
                                    </div>
                                </div>
                            </div>

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
                                <label class="col-md-4 control-label"><?php echo __("E-mail"); ?></label>  
                                <div class="col-md-8 inputGroupContainer">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                        <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" class="form-control"  type="email" value="" required >
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
                <div class="col-xs-1 col-sm-1 col-lg-8"></div>
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
                                url: 'createUser',
                                data: {"user": $('#inputUser').val(), "pass": $('#inputPassword').val(), "email": $('#inputEmail').val(), "name": $('#inputName').val()},
                                type: 'post',
                                success: function (response) {
                                    if (response.status > 0) {
                                        swal({
                                            title: "<?php echo __("Congratulations!"); ?>",
                                            text: "<?php echo __("Your user has been created!"); ?>",
                                            type: "success"
                                        },
                                                function () {
                                                    window.location.href = '<?php echo $global['webSiteRootURL']; ?>user';
                                                });
                                    } else {
                                        if (response.error) {
                                            swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                                        } else {
                                            swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user has NOT been created!"); ?>", "error");
                                        }
                                    }
                                    modal.hidePleaseWait();
                                }
                            });
                            return false;
                        }
                    });
                });
            </script>
        </div><!--/.container-->

        <?php
        include 'include/footer.php';
        ?>

    </body>
</html>
