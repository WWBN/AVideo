<?php
CustomizeUser::autoIncludeBGAnimationFile();
if (empty($_GET['redirectUri'])) {
    if (!empty($_SERVER["HTTP_REFERER"])) {
        // if comes from the streamer domain
        if (preg_match('#^' . $global['webSiteRootURL'] . '#i', $_SERVER["HTTP_REFERER"]) === 1) {
            $_GET['redirectUri'] = $_SERVER["HTTP_REFERER"];
        }
    }
}
if (empty($signUpURL)) {
    $signUpURL = "{$global['webSiteRootURL']}signUp";
    if (isValidURL(@$_GET['redirectUri'])) {
        $signUpURL = addQueryStringParameter($signUpURL, 'redirectUri', $_GET['redirectUri']);
    }
}
?>
<br>
<?php
if (empty($_COOKIE) && get_browser_name() !== 'Other (Unknown)') {
?>
    <div style="padding: 10px;">
        <div class="alert alert-warning">
            <h1><i class="fas fa-exclamation-circle"></i> <?php echo __("Login Alert"); ?></h1>
            <h2><?php echo __("Please Login in the window pop up"); ?></h2>
            <button class="btn btn-block btn-warning" onclick="openLoginWindow()"><i class="fas fa-sign-in-alt"></i> <?php echo __("Open pop-up Login window"); ?></button><br>
            <?php echo __("In case the login window does not open, check how do I disable the pop-up blocker in your browser"); ?>:<br>
            <a href="https://support.mozilla.org/en-US/kb/pop-blocker-settings-exceptions-troubleshooting" target="_blank">Mozilla Firefox</a><br>
            <a href="https://support.google.com/chrome/answer/95472" target="_blank">Google Chrome</a>
        </div>
    </div>
    <script>
        function openLoginWindow() {
            win = window.open('<?php echo $global['webSiteRootURL']; ?>user?redirectUri=<?php print $_GET['redirectUri'] ?? ""; ?>', 'Login Page', "width=640,height=480,scrollbars=no");
        }
        var win;
        openLoginWindow();
        var logintimer = setInterval(function() {
            if (win.closed) {
                clearInterval(logintimer);
                document.location = "<?php print $_GET['redirectUri'] ?? $global['webSiteRootURL']; ?>";
            }
        }, 1000);
        $(document).ready(function() {
            if (!win || win.closed || typeof win.closed == 'undefined') {
                //avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("In order to enjoy our login feature, you need to allow our pop-ups in your browser."); ?>", "error");
            }
        });
    </script>
<?php
    return false;
}
?>
<div class="row loginPage">
    <div class="hidden-xs col-sm-2 col-md-3 "></div>
    <div class="col-xs-12 col-sm-8  col-md-6">

        <?php
        if (empty($advancedCustomUser->disableNativeSignIn)) {
        ?>
            <div class="panel panel-default <?php
                                            echo getCSSAnimationClassAndStyle();
                                            getCSSAnimationClassAndStyleAddWait(0.5);
                                            ?>">
                <div class="panel-heading">
                    <?php
                    //var_dump($_GET['redirectUri'], getRedirectUri());
                    if (emptyHTML($advancedCustomUser->messageReplaceWelcomeBackLoginBox->value)) {
                    ?>
                        <h2 class="<?php echo getCSSAnimationClassAndStyle(); ?>">
                            <?php echo __('Welcome back!'); ?>
                        </h2>
                        <div class="">
                            <?php
                            if (!empty($advancedCustomUser->userMustBeLoggedInCloseButtonURL)) {
                            ?>
                                <div class="pull-right">
                                    <a id="buttonMyNavbar" class=" btn btn-default navbar-btn" style="padding: 6px 12px; margin-right: 40px;" href="<?php echo $advancedCustomUser->userMustBeLoggedInCloseButtonURL; ?>">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php
                    } else {
                        echo $advancedCustomUser->messageReplaceWelcomeBackLoginBox->value;
                    }
                    ?>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" id="loginForm">
                        <input type="hidden" name="redirectUri" value="" />
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                            <label class="col-sm-4 control-label"><?php echo !empty($advancedCustomUser->forceLoginToBeTheEmail) ? __("E-mail") : __("User"); ?></label>
                            <div class="col-sm-8 inputGroupContainer">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                    <input id="inputUser" placeholder="<?php echo !empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control" type="text" value="" required>
                                </div>
                            </div>
                        </div>


                        <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                            <label class="col-sm-4 control-label"><?php echo __("Password"); ?></label>
                            <div class="col-sm-8 inputGroupContainer">
                                <?php getInputPassword("inputPassword"); ?>
                            </div>
                        </div>

                        <?php $captcha = User::getCaptchaForm(); ?>
                        <div class="form-group captcha" style="<?php echo User::isCaptchaNeed() ? "" : "display: none;" ?>" id="captchaForm">
                            <?php echo $captcha['content']; ?>
                        </div>
                        <?php
                        if (empty($hideRememberMe)) {
                        ?>
                            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                <div class="col-xs-4 text-right">
                                    <label for="inputRememberMe"><?php echo __("Remember me"); ?></label>
                                </div>
                                <div class="col-xs-8">
                                    <div class="material-switch" data-toggle="tooltip" title="<?php echo __("Check this to stay signed in"); ?>">
                                        <input id="inputRememberMe" class="form-control" type="checkbox">
                                        <label for="inputRememberMe" class="label-success"></label>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                        <!-- Button -->
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-success  btn-block <?php echo getCSSAnimationClassAndStyle(); ?>" id="mainButton"><span class="fas fa-sign-in-alt"></span> <?php echo __("Sign in"); ?></button>
                            </div>
                        </div>
                        <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                            <div class="col-xs-12 inputGroupContainer text-center">
                                <button type="button" class="btn btn-default btn-xs <?php echo getCSSAnimationClassAndStyle(); ?>" id="forgotPassword" data-toggle="tooltip" title="<?php echo __("Use this to recover your password"); ?>">
                                    <i class="fas fa-redo-alt"></i> <?php echo __("I forgot my password"); ?>
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="panel-footer">
                    <?php
                    if (empty($advancedCustomUser->disableNativeSignUp)) {
                    ?>
                        <div class="row <?php echo getCSSAnimationClassAndStyle(); ?>" data-toggle="tooltip" title="<?php echo __("Are you new here?"); ?>">
                            <div class="col-md-12">
                                <a href="<?php echo $signUpURL; ?>" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> <?php echo __("Sign up"); ?></a>
                            </div>
                        </div>
                    <?php
                    }
                    if (!empty($_REQUEST['cancelUri']) && isValidURL($_REQUEST['cancelUri'])) {
                    ?>
                        <div class="row <?php echo getCSSAnimationClassAndStyle(); ?>">
                            <div class="col-md-12">
                                <a href="<?php echo $_REQUEST['cancelUri']; ?>" class="btn btn-link btn-block"><i class="fas fa-arrow-left"></i> <?php echo __("Cancel"); ?></a>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        <?php
        }
        ?>
        <?php
        $login = AVideoPlugin::getLogin();
        $totalLogins = 0;
        foreach ($login as $value) {
            if (is_string($value) && file_exists($value)) { // it is a include path for a form
                include $value;
            } elseif (is_array($value)) {
                $totalLogins++;
            }
        }

        //var_dump($totalLogins, $login);exit;
        $columSize = 12;
        if ($totalLogins > 1) {
            switch ($totalLogins) {
                case 2:
                case 4:
                case 5:
                case 7:
                case 8:
                case 10:
                case 11:
                    $columSize = 6;
                    break;
                case 3:
                case 6:
                case 9:
                case 12:
                    $columSize = 4;
                    break;
            }
        }
        $loginCount = 0;
        foreach ($login as $value) {
            if (is_string($value) && file_exists($value)) {
                //include $value;
            } elseif (is_array($value)) {
                $loginCount++;
                $uid = _uniqid();
                $oauthURL = "{$global['webSiteRootURL']}login?type={$value['parameters']->type}&redirectUri=" . ($_GET['redirectUri'] ?? "");
                $loginBtnLabel = "<span class=\"{$value['parameters']->icon}\"></span> {$value['parameters']->type}";
                if (!empty($value['dataObject']->buttonLabel)) {
                    $loginBtnLabel = $value['dataObject']->buttonLabel;
                }
        ?>
                <div class="col-md-<?php echo $columSize; ?> <?php echo getCSSAnimationClassAndStyle('animate__fadeInUp'); ?>">
                    <button id="login<?php echo $uid; ?>" class="<?php echo $value['parameters']->class; ?>"><?php echo $loginBtnLabel; ?></button>
                </div>
                <script>
                    $(document).ready(function() {
                        $('#login<?php echo $uid; ?>').click(function() {
                            modal.showPleaseWait();
                            if (typeof inIframe !== 'undefined' && inIframe()) {
                                var popup = window.open('<?php echo $oauthURL; ?>', 'loginYPT');
                                var popupTick = setInterval(function() {
                                    if (popup.closed) {
                                        clearInterval(popupTick);
                                        console.log('window closed!');
                                        location.reload();
                                    }
                                }, 500);
                            } else {
                                document.location = "<?php echo $oauthURL; ?>";
                            }
                        });
                    });
                </script>
        <?php
            }
        }
        ?>
        <?php
        if (!empty($advancedCustomUser->messageToAppearBelowLoginBox->value)) {
            echo "<div class='alert alert-info'> <i class=\"fas fa-info-circle\"></i> ";
            echo $advancedCustomUser->messageToAppearBelowLoginBox->value;
            echo "</div>";
        }
        ?>
    </div>
    <div class="hidden-xs col-sm-2 col-md-3"></div>
</div>
<script>
    function loginFormActive() {

    }

    function loginFormReset() {

    }
    $(document).ready(function() {
        <?php
        if (!empty($_GET['error'])) {
        ?>
            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo addslashes($_GET['error']); ?>", "error");
        <?php
        }
        ?>
        $('#loginForm').submit(function(evt) {
            evt.preventDefault();
            if (!$('#inputUser').val()) {
                avideoAlertError('<?php echo __('Please type your username'); ?>');
                return false;
            }
            if (!$('#inputPassword').val()) {
                avideoAlertError('<?php echo __('Please type your password'); ?>');
                return false;
            }
            <?php
            if (!empty($advancedCustomUser->forceLoginToBeTheEmail)) {
            ?>
                var email = $("#inputUser").val();
                if (!validateEmail(email) && email.toLowerCase() !== "admin") {
                    // if the user is admin, let it go
                    //avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("The username must be an email"); ?>", "error");
                    //return false;
                    avideoToastWarning('<?php echo __('This is not a valid email'); ?>');
                }
            <?php
            }
            ?>
            modal.showPleaseWait();
            loginFormActive();
            $.ajax({
                url: webSiteRootURL + 'objects/login.json.php',
                data: {
                    user: $('#inputUser').val(),
                    pass: $('#inputPassword').val(),
                    rememberme: $('#inputRememberMe').is(":checked"),
                    captcha: <?php echo empty($captcha['captchaText'])?"''":$captcha['captchaText']; ?>,
                    redirectUri: '<?php echo $_GET['redirectUri'] ?? ''; ?>'
                },
                type: 'post',
                success: async function(response) {
                    if (!response.isLogged) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user or password is wrong!"); ?>", "error");
                        }
                        if (response.isCaptchaNeed) {
                            <?php echo $captcha['btnReloadCapcha']; ?>
                            $('#captchaForm').slideDown();
                        }
                        loginFormReset();
                    } else {
                        var url = response.redirectUri;
                        if (inIframe()) {
                            url = addGetParam(url, 'PHPSESSID', response.PHPSESSID);
                        }
                        console.log('Login success', url);
                        await sendAVideoMobileMessage('saveSessionUser', {
                            site: webSiteRootURL,
                            user: $('#inputUser').val(),
                            pass: $('#inputPassword').val()
                        });
                        document.location = url;
                    }
                }
            });
        });
        $('#forgotPassword').click(function() {
            _forgotPass();
        });
    });
    <?php $captcha2 = User::getCaptchaForm(); ?>

    function _forgotPass() {
        var user = $('#inputUser').val();
        if (!user) {
            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("You need to inform what is your user!"); ?>", "error");
            return false;
        }
        var capcha = <?php echo json_encode($captcha2['html']); ?>;
        var span = document.createElement("span");
        span.innerHTML = "<?php echo __("We will send you a link, to your e-mail, to recover your password!"); ?>" + capcha;
        swal({
            title: "<?php echo __("Are you sure?"); ?>",
            content: span,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then(function(willDelete) {
            console.log('forgotPassword', willDelete);
            if (willDelete) {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL+'objects/userRecoverPass.php',
                    data: {
                        "user": $('#inputUser').val(),
                        "captcha": <?php echo $captcha2['captchaText']; ?>
                    },
                    type: 'post',
                    success: function(response) {
                        if (response.error) {
                            avideoAlert("<?php echo __("Error"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("E-mail sent"); ?>", "<?php echo __("We sent you an e-mail with instructions"); ?>", "success");
                        }
                        modal.hidePleaseWait();
                    }
                });
            }
        });
        eval(<?php echo json_encode($captcha2['script']); ?>);
    }
</script>