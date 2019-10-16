<div class="row">
    <div class="hidden-xs col-sm-2 col-md-3 col-lg-4"></div>
    <div class="col-xs-12 col-sm-8  col-md-6 col-lg-4 list-group-item ">
        <fieldset>
            <legend><?php echo __("Please sign in"); ?></legend>


            <?php
            if (empty($advancedCustomUser->disableNativeSignIn)) {
                ?>
                <form class="form-compact well form-horizontal"  id="loginForm">

                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input  id="inputUser" placeholder="<?php echo !empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="<?php echo empty($advancedCustomUser->forceLoginToBeTheEmail) ? "text" : "email"; ?>" value="" required >
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
                        </div>
                    </div>

                    <?php
                    $captcha = User::getCaptchaForm();
                    ?>
                    <div class="form-group" style="<?php echo User::isCaptchaNeed() ? "" : "display: none;" ?>" id="captchaForm">
                        <?php echo $captcha; ?>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label"><?php echo __("Remember me"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="material-switch">
                                <input  id="inputRememberMe" class="form-control"  type="checkbox">
                                <label for="inputRememberMe" class="label-success"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label"></label>
                        <div class="col-md-8 inputGroupContainer">
                            <?php
                            if (empty($advancedCustomUser->disableNativeSignUp)) {
                                ?>
                                <small><a href="#" class="btn" id="forgotPassword"><?php echo __("I forgot my password"); ?></a></small>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <!-- Button -->
                    <div class="form-group">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success  btn-block" id="mainButton" ><span class="fas fa-sign-in-alt"></span> <?php echo __("Sign in"); ?></button>
                        </div>
                    </div>

                </form>
                <?php
                if (empty($advancedCustomUser->disableNativeSignUp)) {
                    ?>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="signUp?redirectUri=<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""; ?>" class="btn btn-primary btn-block" ><span class="fa fa-user-plus"></span> <?php echo __("Sign up"); ?></a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
            <hr>
            <?php
            $login = YouPHPTubePlugin::getLogin();
            foreach ($login as $value) {
                if (is_string($value) && file_exists($value)) { // it is a include path for a form
                    include $value;
                } else if (is_array($value)) {
                    ?>
                    <div class="col-md-6">
                        <a href="login?type=<?php echo $value['parameters']->type; ?>" class="<?php echo $value['parameters']->class; ?>" ><span class="<?php echo $value['parameters']->icon; ?>"></span> <?php echo $value['parameters']->type; ?></a>
                    </div>
                    <?php
                }
            }
            ?>
            <hr>
        </fieldset>
        <?php

        if (!empty($advancedCustomUser->messageToAppearBelowLoginBox->value)) {
            echo "<div class='alert alert-info'>";
            echo $advancedCustomUser->messageToAppearBelowLoginBox->value;
            echo "</div>";
        }
        ?>
    </div>
    <div class="hidden-xs col-sm-2 col-md-3 col-lg-4"></div>
</div>
<script>
    $(document).ready(function () {
<?php
if (!empty($_GET['error'])) {
    ?>
            swal("<?php echo __("Sorry!"); ?>", "<?php echo addslashes($_GET['error']); ?>", "error");
    <?php
}
?>
        $('#loginForm').submit(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: '<?php echo $global['webSiteRootURL']; ?>objects/login.json.php',
                data: {"user": $('#inputUser').val(), "pass": $('#inputPassword').val(), "rememberme": $('#inputRememberMe').is(":checked"), "captcha": $('#captchaText').val(), "redirectUri": "<?php print isset($_GET['redirectUri']) ? $_GET['redirectUri'] : ""; ?>"},
                type: 'post',
                success: function (response) {
                    if (!response.isLogged) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            swal("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            swal("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user or password is wrong!"); ?>", "error");
                        }
                        if (response.isCaptchaNeed) {
                            $("#btnReloadCapcha").trigger('click');
                            $('#captchaForm').slideDown();
                        }
                    } else {

                        document.location = response.redirectUri;
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
            var capcha = '<span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha" id="captcha"></span><span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span><input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText2">';
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
                            url: '<?php echo $global['webSiteRootURL']; ?>objects/userRecoverPass.php',
                            data: {"user": $('#inputUser').val(), "captcha": $('#captchaText2').val()},
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
    });

</script>
