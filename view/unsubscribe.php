<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}

if (empty($_REQUEST['users_id'])) {
    forbiddenPage('Invalid users_id');
}

if (!User::isLogged()) {
    gotToLoginAndComeBackHere('');
}

$row = Subscribe::getSubscribeFromID(User::getId(), $_REQUEST['users_id'], '');

if (empty($row)) {
    forbiddenPage('Invalid subscription');
}

$subscribe = new Subscribe($row['id']);
$subscribe->setNotify(0);
$subscribe->save();

?>
<!DOCTYPE html>
<html lang="<?php echo $config->getLanguage(); ?>">
    <head>
        <title><?php echo __("Unsubscribe"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>

    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
            <br>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php
                    echo Video::getCreatorHTML($_REQUEST['users_id']);
                    ?>
                </div>
                <div class="panel-body">
                    <h1><?php echo __("You've unsubscribed"); ?></h1>
                    <?php echo __(" You'll no longer receive emails from us"); ?>
                </div>
            </div>

        </div><!--/.container-->
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>

        <script>
            $(document).ready(function () {



            });

        </script>
    </body>
</html>
