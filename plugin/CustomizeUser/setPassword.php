<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage('Must be admin');
}
$users_id = intval(@$_REQUEST['users_id']);
if (empty($users_id)) {
    forbiddenPage('Empty users_id');
}
$pass = User::getProfilePassword($users_id);
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Set Password"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php
                echo Video::getCreatorHTML($users_id);
                ?>
            </div>
            <div class="panel-body">
                Protect the user's channel with a password 
                <?php
                   echo getInputPassword('ProfilePassword', 'maxlength="100" value="'.$pass.'" class="form-control"', __('Channel Password'));
                ?>
            </div>
            <div class="panel-footer">
                <button class="btn btn-success btn-lg btn-block" onclick="setSubscribers();">
                    <i class="fas fa-save"></i> <?php echo __('Save'); ?>
                </button>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script type="text/javascript">
            function setSubscribers() {
                modal.showPleaseWait();
                $.ajax({
                    url: webSiteRootURL + 'plugin/CustomizeUser/setPassword.json.php',
                    method: 'POST',
                    data: {
                        users_id: <?php echo $users_id; ?>,
                        ProfilePassword: $('#ProfilePassword').val()
                    },
                    success: function (response) {
                        modal.hidePleaseWait();
                        avideoResponse(response);
                    }
                });
            }
            $(document).ready(function () {

                    $('#ProfilePassword').keyup(function () {
                        $(this).val($(this).val().replace(/[^0-9a-z]/i, ''));
                    });
                });
        </script>
    </body>
</html>
