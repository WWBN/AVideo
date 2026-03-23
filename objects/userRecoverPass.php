<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';

global $global, $config;
$global['ignoreAllCache'] = 1;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
if (!(!empty($_REQUEST['user']) && !empty($_REQUEST['recoverpass']))) {
    _error_log("RecoverPass start user={$_POST['user']} " .' IP='.getRealIpAddr().' '. ' Line='.__LINE__.' '.$_SERVER['HTTP_USER_AGENT'] . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

    $obj = new stdClass();
    $obj->user = $_REQUEST['user'];
    $obj->captcha = $_REQUEST['captcha'];
    $obj->reloadCaptcha = false;
    $obj->session_id = session_id();
    /*
    $obj->post = $_POST;
    $obj->get = $_GET;
    $obj->input = file_get_contents("php://input");
    $obj->request = $_REQUEST;
    */
    header('Content-Type: application/json');

    if (empty($_REQUEST['captcha'])) {
        $obj->error = __("Captcha is empty");
        die(json_encode($obj));
    }
    require_once 'captcha.php';
    $valid = Captcha::validation($_REQUEST['captcha']);
    if (!$valid) {
        $obj->error = __("Your code is not valid");
        $obj->reloadCaptcha = true;
        die(json_encode($obj));
    }

    $user = new User(0, $_REQUEST['user'], false);
    if (empty($user->getStatus()) || $user->getStatus() !== 'a' || empty($user->getEmail())) {
        $obj->success = __("Message sent");
        die(json_encode($obj));
    }

    $recoverPass = $user->setRecoverPass();
    if ($user->save()) {

        if (empty($advancedCustomUser)) {
            $advancedCustomUser = AVideoPlugin::getObjectData("CustomizeUser");
        }

        $url = "{$global['webSiteRootURL']}recoverPass";
        $url = addQueryStringParameter($url, 'user', $_REQUEST['user']);
        $url = addQueryStringParameter($url, 'recoverpass', $recoverPass);

        $to = $user->getEmail();
        $subject = __($advancedCustomUser->recoverPassSubject) . ' ' . $config->getWebSiteTitle();
        $message = __($advancedCustomUser->recoverPassText) . "<br><a href='{$url}' class='button blue-button'>" . __("Reset password") . "</a><br>IP: " . getRealIpAddr();
        $fromEmail = $config->getContactEmail();
        $resp = sendSiteEmail($to, $subject, $message, $fromEmail);

        //send the message, check for errors
        if (!$resp) {
            $obj->error = __("Message could not be sent") . " " . $mail->ErrorInfo;
        } else {
            $obj->success = __("Message sent");
        }
    } else {
        $obj->error = __("Recover password could not be saved!");
    }
    die(json_encode($obj));
} else {
    _error_log("RecoverPass start user={$_POST['user']} " .' IP='.getRealIpAddr().' '. ' Line='.__LINE__.' '.$_SERVER['HTTP_USER_AGENT'] . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

    $user = new User(0, $_REQUEST['user'], false);

    $readonly = '';
    if ($user->getRecoverPass() !== $_REQUEST['recoverpass']) {
        //forbiddenPage('The recover pass does not match!');
        $recoverPass = '';
    } else {
        $readonly = 'readonly';
        $recoverPass = $user->getRecoverPass();
    }
    $_page = new Page(array('Recover Password'));
?>
    <div class="container">
        <form action="" method="post" id="recoverPassForm">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2><?php echo __("Recover password!"); ?></h2>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <label class="col-md-4 control-label"><?php echo __("User"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa-solid fa-user"></i></span>
                                <input name="user" class="form-control" type="text" value="<?php echo $user->getUser(); ?>" readonly>
                            </div>
                        </div>
                        <label class="col-md-4 control-label"><?php echo __("Recover Password"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input name="recoverPassword" class="form-control" type="text" value="<?php echo $recoverPass; ?>" <?php echo $readonly; ?>>
                            </div>
                        </div>

                        <label class="col-md-4 control-label"><?php echo __("New Password"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <?php getInputPassword("newPassword", 'class="form-control" required="required" autocomplete="off"', __("New Password")); ?>
                        </div>

                        <label class="col-md-4 control-label"><?php echo __("Confirm New Password"); ?></label>
                        <div class="col-md-8 inputGroupContainer">
                            <?php getInputPassword("newPasswordConfirm", 'class="form-control" required="required" autocomplete="off"', __("Confirm New Password")); ?>
                        </div>

                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-regular fa-floppy-disk"></i>
                        <?php echo __("Save Password"); ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('#recoverPassForm').submit(function(evt) {
                evt.preventDefault();
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'objects/userRecoverPassSave.json.php',
                    data: $('#recoverPassForm').serializeArray(),
                    type: 'post',
                    success: function(response) {
                        modal.hidePleaseWait();
                        if (!response.error) {
                            avideoAlert("<?php echo __("Congratulations!"); ?>", "<?php echo __("Your new password has been set!"); ?>", "success");
                        } else {
                            avideoAlert("<?php echo __("Your new password could not be set!"); ?>", response.error, "error");
                        }
                    }
                });
                return false;
            });

        });
    </script>
<?php
    $_page->print();
    exit;
}
