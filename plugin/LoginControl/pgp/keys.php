<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("LoginControl");
require_once $global['systemRootPath'] . 'plugin/LoginControl/pgp/functions.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?php echo __("PGP Keys"); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <style>
            .monospacedKey{
                font-family: 'Courier New', monospace;
                font-size: 0.8em;
            }
        </style>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <br>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading tabbable-line">
                    <ul class="nav nav-tabs">
                        <li class="nav-item active">
                            <a class="nav-link " href="#decryptMessage" data-toggle="tab">
                                <i class="fas fa-unlock"></i> <?php echo __('Decrypt Message') ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="#encryptMessage" data-toggle="tab">
                                <i class="fas fa-lock"></i> <?php echo __('Encrypt Message') ?>
                            </a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link " href="#generateKeys" data-toggle="tab">
                                <i class="fas fa-key"></i> <?php echo __('Generate Keys') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content clearfix">
                        <div class="tab-pane active" id="decryptMessage">
                            <?php
                            include $global['systemRootPath'] . 'plugin/LoginControl/pgp/decryptMessage.php';
                            ?>
                        </div>
                        <div class="tab-pane" id="encryptMessage">
                            <?php
                            include $global['systemRootPath'] . 'plugin/LoginControl/pgp/encryptMessage.php';
                            ?>
                        </div>
                        <div class="tab-pane " id="generateKeys">
                            <?php
                            include $global['systemRootPath'] . 'plugin/LoginControl/pgp/generateKeys.php';
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
