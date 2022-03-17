<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (User::isLogged()) {
    header("Location: {$global['webSiteRootURL']}user");
    exit;
}

//$json_file = url_get_contents("{$global['webSiteRootURL']}plugin/CustomizeAdvanced/advancedCustom.json.php");
// convert the string to a json object
//$advancedCustom = _json_decode($json_file);
if (!empty($advancedCustomUser->disableNativeSignUp)) {
    die(__("Sign Up Disabled"));
}

$agreement = AVideoPlugin::loadPluginIfEnabled("SignUpAgreement");

$redirectUri = getRedirectUri($global['webSiteRootURL']);
$siteRedirectUri = "{$global['webSiteRootURL']}user";
$siteRedirectUri = addQueryStringParameter($siteRedirectUri, 'redirectUri', $redirectUri);

if (isValidURL(@$_GET['siteRedirectUri'])) {
    $siteRedirectUri = $_GET['siteRedirectUri'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Sign Up") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        CustomizeUser::autoIncludeBGAnimationFile();
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <br>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
                <div class="col-xs-12 col-sm-12 col-lg-8">
                    <div class="panel panel-default <?php
                    echo getCSSAnimationClassAndStyle();
                    getCSSAnimationClassAndStyleAddWait(0.5);
                    ?>">
                        <div class="panel-heading tabbable-line">
                            <ul class="nav nav-tabs" id="signupNavTabs">
                                <li class="nav-item active" id="sinupBasic">
                                    <a class="nav-link " href="#" data-toggle="tab" onclick="showCompanyFields(false)">
                                        <i class="fas fa-user"></i>
                                        <?php echo __("Sign Up"); ?>
                                    </a>
                                </li>
                                <?php
                                if (empty($advancedCustomUser->disableCompanySignUp)) {
                                    ?>
                                    <li class="nav-item" id="sinupCompany">
                                        <a class="nav-link " href="#" data-toggle="tab" onclick="showCompanyFields(true)">
                                            <i class="fas fa-building"></i>
                                            <?php echo __("Company Sign Up"); ?>
                                        </a>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                        <div class="panel-body">

                            <div class="tab-content" id="signupTabContent">
                                <div class="tab-pane active" id="signupRegular" >

                                    <form id="updateUserForm" onsubmit="">
                                        <div class="form-group">
                                            <div class="col-md-12 inputGroupContainer">
                                                <div class="input-group">
                                                    <?php
                                                    if (!empty($advancedCustomUser->messageToAppearAboveSignUpBox->value)) {
                                                        echo $advancedCustomUser->messageToAppearAboveSignUpBox->value;
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <label class="col-sm-4 control-label hidden-xs" for="inputName"><?php echo __("Name"); ?></label>
                                            <div class="col-sm-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-pencil"></i></span>
                                                    <input  id="inputName" placeholder="<?php echo __("Name"); ?>" name="name" class="form-control"  type="text" value="" required >
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <label class="col-sm-4 control-label hidden-xs" for="inputUser"><?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? __("E-mail") : __("User"); ?></label>
                                            <div class="col-sm-8 inputGroupContainer">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                                    <input  id="inputUser" placeholder="<?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="<?php echo empty($advancedCustomUser->forceLoginToBeTheEmail) ? "text" : "email"; ?>" value="" name="user" required >
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        if (empty($advancedCustomUser->forceLoginToBeTheEmail)) {
                                            ?>
                                            <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                                <label class="col-sm-4 control-label hidden-xs" for="inputEmail"><?php echo __("E-mail"); ?></label>
                                                <div class="col-sm-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></span>
                                                        <input  id="inputEmail" placeholder="<?php echo __("E-mail"); ?>" name="email" class="form-control"  type="email" value="" required >
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                        <?php
                                        if (empty($advancedCustomUser->doNotShowPhoneOnSignup)) {
                                            ?>
                                            <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                                <label class="col-sm-4 control-label hidden-xs" for="phone"><?php echo __("Phone"); ?></label>
                                                <div class="col-sm-8 inputGroupContainer">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fas fa-phone"></i></span>
                                                        <input  id="phone" placeholder="<?php echo __("Phone"); ?>" name="phone" class="form-control"  type="text" value="" >
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <label class="col-sm-4 control-label hidden-xs" for="inputPassword"><?php echo __("New Password"); ?></label>
                                            <div class="col-sm-8 inputGroupContainer">
                                                <?php
                                                getInputPassword("inputPassword", 'class="form-control" autocomplete="off" ', __("New Password"));
                                                ?>
                                            </div>
                                        </div>

                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <label class="col-sm-4 control-label hidden-xs" for="inputPasswordConfirm"><?php echo __("Confirm New Password"); ?></label>
                                            <div class="col-sm-8 inputGroupContainer">
                                                <?php
                                                getInputPassword("inputPasswordConfirm", 'class="form-control" autocomplete="off" ', __("Confirm New Password"));
                                                ?>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <?php
                                        if (empty($advancedCustomUser->disableCompanySignUp)) {
                                            $extra_info_fields = Users_extra_info::getAllActive(0, true);
                                            echo '<!-- Show CompanySignUp -->';
                                            echo '<input id="is_company" name="is_company" type="hidden" value="' . User::$is_company_status_WAITINGAPPROVAL . '" >';
                                        } else {
                                            $extra_info_fields = Users_extra_info::getAllActive();
                                            echo '<!-- DO NOT Show CompanySignUp -->';
                                        }
                                        //var_dump($extra_info_fields);
                                        foreach ($extra_info_fields as $value) {
                                            if (Users_extra_info::isCompanyOnlyField($value['status'])) {
                                                $class = 'companyField';
                                                $style = 'display: none;';
                                                ?>
                                                <div class="row form-group <?php echo $class; ?>" style="<?php echo $style; ?>">
                                                    <?php echo Users_extra_info::typeToHTML($value, 'col-sm-4 control-label hidden-xs', 'col-sm-8 inputGroupContainer'); ?>
                                                </div>
                                                <?php
                                            } else {
                                                ?>
                                                <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                                                    <?php echo Users_extra_info::typeToHTML($value, 'col-sm-4 control-label hidden-xs', 'col-sm-8 inputGroupContainer'); ?>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <div class="clearfix"></div>    
                                            <?php
                                        }
                                        ?>
                                        <div class="clearfix"></div>
                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <?php
                                            if (!empty($agreement)) {
                                                $agreement->getSignupCheckBox();
                                            }
                                            ?>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="row form-group <?php echo getCSSAnimationClassAndStyle(); ?>">
                                            <label class="col-sm-4 control-label hidden-xs" for="captchaText"><?php echo __("Type the code"); ?></label>
                                            <div class="col-sm-8 inputGroupContainer captcha">
                                                <div class="input-group">
                                                    <span class="input-group-addon"><img src="<?php echo $global['webSiteRootURL']; ?>captcha?PHPSESSID=<?php echo session_id(); ?>&<?php echo time(); ?>" id="captcha"></span>
                                                    <span class="input-group-addon"><span class="btn btn-xs btn-success" id="btnReloadCapcha"><span class="glyphicon glyphicon-refresh"></span></span></span>
                                                    <input name="captcha" placeholder="<?php echo __("Type the code"); ?>" class="form-control" type="text" style="height: 60px;" maxlength="5" id="captchaText">
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                </div>
                            </div>

                        </div>
                        <div class="panel-footer">
                            <button class="btn btn-primary btn-block btn-lg <?php echo getCSSAnimationClassAndStyle(); ?>" onclick="$('#updateUserForm').submit();" ><i class="fas fa-user-plus"></i> <?php echo __("Sign Up"); ?></button>
                            <?php
                            if (!empty($redirectUri)) {
                                ?>
                                <a href="<?php echo $redirectUri; ?>" class="btn btn-default btn-block btn-xs <?php echo getCSSAnimationClassAndStyle(); ?>" ><i class="fas fa-times"></i> <?php echo __("Cancel"); ?></a>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                </div>
                <div class="col-xs-12 col-sm-12 col-md-2"></div>
            </div>
            <script>
                function showCompanyFields(show) {
                    $('#signupNavTabs .nav-item').removeClass('active');
                    if (show) {
                        $('#sinupCompany').addClass('active');
                        $('.companyField').slideDown();
                        $('#is_company').val(<?php echo User::$is_company_status_WAITINGAPPROVAL; ?>);
                    } else {
                        $('#sinupBasic').addClass('active');
                        $('.companyField').slideUp();
                        $('#is_company').val(0);
                    }
                }

                function validateSignupForm() {
                    var errorFound = false;
                    var errorClass = 'glowBox';
                    $('#updateUserForm .input-group').removeClass(errorClass);
                    $('#updateUserForm input').each(function () {
                        console.log('found', $(this).attr('name'));
                        if ($(this).prop('required') && $(this).is(":visible")) {
                            if ($(this).attr('type') === 'checkbox') {
                                if (!$(this).is(':checked')) {
                                    $(this).closest('.input-group').addClass(errorClass);
                                    errorFound = 'Confirmation Required';
                                    return false;
                                }
                            } else {
                                console.log('is required', $(this).attr('name'));
                                if (!$(this).val().match(/[0-9a-z]+/i)) {
                                    $(this).closest('.input-group').addClass(errorClass);
                                    var label = $("label[for='" + $(this).attr('id') + "']").text();
                                    if (!label) {
                                        label = $(this).attr('name');
                                    }
                                    errorFound = label + ' is required';
                                    return false;
                                }
                            }
                        }
                    });
                    console.log(errorFound);
                    if (errorFound) {
                        avideoAlertError(errorFound);
                        return false;
                    }

                    var pass1 = $('#inputPassword').val();
                    var pass2 = $('#inputPasswordConfirm').val();
                    // Password doesn't match
                    if (!pass1.match(/[0-9a-z]+/i)) {
                        $('#inputPassword').closest('.input-group').addClass(errorClass);
                        avideoAlertError("<?php echo __("Your password cannot be blank"); ?>");
                        return false;
                    }
                    if (pass1 != pass2) {
                        $('#inputPassword').closest('.input-group').addClass(errorClass);
                        $('#inputPasswordConfirm').closest('.input-group').addClass(errorClass);
                        avideoAlertError("<?php echo __("Your password does not match!"); ?>");
                        return false;
                    }
                    if (!$('#captchaText').val().match(/^[0-9a-z]{5}$/i)) {
                        $('#captchaText').closest('.input-group').addClass(errorClass);
                        avideoAlertError("<?php echo __("The captcha is wrong"); ?>");
                        return false;
                    }
                    if ($('#inputEmail').is(":visible") && !isEmailValid($('#inputEmail').val())) {
                        if (!isEmailValid($('#inputUser').val())) {
                            $('#inputEmail').closest('.input-group').addClass(errorClass);
                            avideoAlertError("<?php echo __("You must specify a valid email"); ?>");
                            return false;
                        }
                    }
                    if (!$('#inputUser').val().match(/^[0-9a-z@._-]{3,}$/i) && !isEmailValid($('#inputUser').val()) ) {
                        $('#inputUser').closest('.input-group').addClass(errorClass);
                        avideoAlertError("<?php echo __("Invalid user"); ?>");
                        return false;
                    }

                    return true;
                }

                $(document).ready(function () {

                    $('#btnReloadCapcha').click(function () {
                        $('#captcha').attr('src', '<?php echo $global['webSiteRootURL']; ?>captcha?PHPSESSID=<?php echo session_id(); ?>&' + Math.random());
                        $('#captchaText').val('');
                    });

                    $('#updateUserForm').submit(function (evt) {
                        evt.preventDefault();
                        if (validateSignupForm()) {
                            modal.showPleaseWait();
                            $.ajax({
                                url: webSiteRootURL + 'objects/userCreate.json.php?PHPSESSID=<?php echo session_id(); ?>',
                                data: $('#updateUserForm').serialize(),
                                type: 'post',
                                success: function (response) {
                                    avideoResponse(response);
                                    if (!response.error) {
                                        window.location.href = '<?php echo $siteRedirectUri; ?>';
                                    } else {
                                        modal.hidePleaseWait();
                                    }
                                }
                            });
                            return false;
                        }
                    });
                });
            </script>
        </div><!--/.container-->

        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

    </body>
</html>
