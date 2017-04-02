<?php
require_once '../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $global['webSiteTitle']; ?> :: <?php echo __("User"); ?></title>
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
                                <label for="inputName" class="sr-only"><?php echo __("Name"); ?></label>
                                <input type="text" id="inputName" class="form-control first" placeholder="<?php echo __("Name"); ?>"  value="<?php echo $user->getName(); ?>" required autofocus>
                                <label for="inputUser" class="sr-only"><?php echo __("User"); ?></label>
                                <input type="text" id="inputUser" class="form-control" placeholder="<?php echo __("User"); ?>" value="<?php echo $user->getUser(); ?>" required>
                                <label for="inputEmail" class="sr-only"><?php echo __("E-mail"); ?></label>
                                <input type="email" id="inputEmail" class="form-control" placeholder="<?php echo __("E-mail"); ?>"  value="<?php echo $user->getEmail(); ?>" required>
                                <label for="inputPassword" class="sr-only"><?php echo __("New Password"); ?></label>
                                <input type="password" id="inputPassword" class="form-control" value="" placeholder="<?php echo __("New Password"); ?>" required>
                                <label for="inputPasswordConfirm" class="sr-only"><?php echo __("Confirm New Password"); ?></label>
                                <input type="password" id="inputPasswordConfirm" class="form-control last" value="" placeholder="<?php echo __("Confirm New Password"); ?>" required>

                                <button class="btn btn-lg btn-primary btn-block"><?php echo __("Save"); ?></button>
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
            include 'include/footer.php';
            ?>

        </div><!--/.container-->


    </body>
</html>
