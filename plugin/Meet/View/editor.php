<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Meet");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo __("Meet") . $config->getPageTitleSeparator() . $config->getWebSiteTitle(); ?></title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.css"/>
        <link href="<?php echo getCDN(); ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('Meet') ?>
                    <div class="pull-right">
                        <?php echo AVideoPlugin::getSwitchButton("Meet"); ?>
                    </div>
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#Meet_schedule"><?php echo __("Meet Schedule"); ?></a></li>
                        <li class=""><a data-toggle="tab" href="#Meet_schedule_has_users_groups"><?php echo __("Meet Schedule Has Users Groups"); ?></a></li>
                        <li class=""><a data-toggle="tab" href="#Meet_join_log"><?php echo __("Meet Join Log"); ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="Meet_schedule" class="tab-pane fade in active" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/Meet/View/Meet_schedule/index_body.php';
                            ?>
                        </div>
                        <div id="Meet_schedule_has_users_groups" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/Meet/View/Meet_schedule_has_users_groups/index_body.php';
                            ?>
                        </div>
                        <div id="Meet_join_log" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/Meet/View/Meet_join_log/index_body.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php echo getCDN(); ?>view/css/DataTables/datatables.min.js"></script>
        <script src="<?php echo getCDN(); ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
