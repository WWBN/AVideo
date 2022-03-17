<?php
require_once '../../videos/configuration.php';

if (User::isAdmin() && !empty($_REQUEST['users_id'])) {
    $users_id = intval($_REQUEST['users_id']);
}
if (empty($users_id)) {
    $users_id = User::getId();
}
if (empty($users_id)) {
    forbiddenPage('Empty user ID');
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo __("Login History"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">

            <div class="panel panel-default">
                <div class="panel-heading tabbable-line">
                    <?php
                    echo Video::getCreatorHTML($users_id);
                    ?>
                </div>
                <div class="panel-body">
                    <?php
                            include '../../plugin/LoginControl/profileTabContent.php';
                    ?>
                </div>
            </div>

        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            $(document).ready(function () {
                $('#loginHistory').addClass('in');
            });
        </script>
    </body>
</html>
