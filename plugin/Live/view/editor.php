<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: Live</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
        <link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container-fluid">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo __('Live') ?> 
                    <div class="pull-right">
                        <?php echo AVideoPlugin::getSwitchButton("Live"); ?>
                    </div>
                </div>
                <div class="panel-body">
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#Live_servers"><i class="fas fa-broadcast-tower"></i> <?php echo __("Live Servers"); ?></a></li>
                        <li class=""><a data-toggle="tab" href="#Live_restreams"><?php echo __("Live Restreams"); ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div id="Live_servers" class="tab-pane fade in active" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/Live/view/Live_servers/index_body.php';
                            ?>
                        </div>
                        <div id="Live_restreams" class="tab-pane fade" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams/index_body.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.js"></script>
        <script src="<?php echo $global['webSiteRootURL']; ?>js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
