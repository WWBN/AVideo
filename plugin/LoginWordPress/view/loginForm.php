<?php
$wp = AVideoPlugin::getObjectData("LoginWordPress");
$redirectUri = getRedirectUri();
if (empty($redirectUri)) {
    $redirectUri = $global['webSiteRootURL'];
}

$wpSite = addLastSlash($wp->customWordPressSite);
?>


<div class="panel panel-default <?php
echo getCSSAnimationClassAndStyle();
getCSSAnimationClassAndStyleAddWait(0.5);
?>">
    <div class="panel-heading">
        <?php
        if (emptyHTML($advancedCustomUser->messageReplaceWelcomeBackLoginBox->value)) {
            ?>
            <h2 class="<?php echo getCSSAnimationClassAndStyle(); ?>">
                <?php echo __('Welcome back!'); ?>
            </h2>
            <?php
        }else{
            echo $advancedCustomUser->messageReplaceWelcomeBackLoginBox->value;
        }
        ?>
    </div>
    <div class="panel-body">
        <form class="form-horizontal"  id="WordPressloginForm">
            <input type="hidden" name="redirectUri" value=""/>
            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                <label class="col-sm-4 control-label"><?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? __("E-mail") : __("User"); ?></label>
                <div class="col-sm-8 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="WPinputUser" placeholder="<?php echo!empty($advancedCustomUser->forceLoginToBeTheEmail) ? "me@example.com" : __("User"); ?>" class="form-control"  type="text" value="" required >
                    </div>
                </div>
            </div>


            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                <label class="col-sm-4 control-label"><?php echo __("Password"); ?></label>
                <div class="col-sm-8 inputGroupContainer">
                    <?php getInputPassword("WPinputPassword"); ?>
                </div>
            </div>
            <!--
            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                <div class="col-xs-4 text-right">
                    <label for="WPinputRememberMe" ><?php echo __("Remember me"); ?></label>
                </div>
                <div class="col-xs-8" >
                    <div class="material-switch" data-toggle="tooltip" title="<?php echo __("Check this to stay signed in"); ?>">
                        <input  id="WPinputRememberMe" class="form-control"  type="checkbox">
                        <label for="WPinputRememberMe" class="label-success" ></label>
                    </div>
                </div>
            </div>
            -->
            <!-- Button -->
            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                <div class="col-md-12">
                    <button type="submit" class="btn btn-success  btn-block <?php echo getCSSAnimationClassAndStyle(); ?>" id="WPmainButton" >
                        <span class="fas fa-sign-in-alt"></span> <?php echo __("Sign in"); ?></button>
                </div>
            </div>
            <div class="form-group <?php echo getCSSAnimationClassAndStyle(); ?>" >
                <div class="col-xs-12 inputGroupContainer text-center">
                    <a href="<?php echo $wp->customWordPressSiteForgotMyPasswordURL; ?>" target="_blank" class="btn btn-default btn-xs <?php echo getCSSAnimationClassAndStyle(); ?>"  id="WPforgotPassword" data-toggle="tooltip" title="<?php echo __("Use this to recover your password"); ?>"><i class="fas fa-redo-alt"></i> <?php echo __("I forgot my password"); ?></a>
                </div>
            </div>

        </form>
    </div>
    <div class="panel-footer">
        <div class="row <?php echo getCSSAnimationClassAndStyle(); ?>" data-toggle="tooltip" title="<?php echo __("Are you new here?"); ?>">
            <div class="col-md-12">
                <a href="<?php echo $wp->customWordPressSiteSignUpURL; ?>"
                   class="btn btn-primary btn-block" target="_blank"><i class="fas fa-plus"></i> <?php echo __("Sign up"); ?></a>
            </div>
        </div>
        <?php
        if (!empty($_REQUEST['cancelUri']) && isValidURL($_REQUEST['cancelUri'])) {
            ?>
            <div class="row <?php echo getCSSAnimationClassAndStyle(); ?>">
                <div class="col-md-12">
                    <a href="<?php echo $_REQUEST['cancelUri']; ?>"
                       class="btn btn-link btn-block"><i class="fas fa-arrow-left"></i> <?php echo __("Cancel"); ?></a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#WordPressloginForm').submit(function (evt) {
            evt.preventDefault();
            modal.showPleaseWait();
            $.ajax({
                url: webSiteRootURL + 'plugin/LoginWordPress/view/login.json.php',
                data: {
                    "WPuser": $('#WPinputUser').val(),
                    "WPpass": $('#WPinputPassword').val(),
                    "rememberme": $('#WPinputRememberMe').is(":checked")},
                type: 'post',
                success: function (response) {

                    if (!response.isLogged) {
                        modal.hidePleaseWait();
                        if (response.error) {
                            avideoAlert("<?php echo __("Sorry!"); ?>", response.error, "error");
                        } else {
                            avideoAlert("<?php echo __("Sorry!"); ?>", "<?php echo __("Your user or password is wrong!"); ?>", "error");
                        }
                    } else {
                        document.location = '<?php echo $redirectUri; ?>'
                    }
                }
            });
        });
    });
</script>