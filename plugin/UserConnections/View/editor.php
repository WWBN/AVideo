<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("UserConnections");
$_page = new Page(array('UserConnections'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('UserConnections') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("UserConnections"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Users_connections"><?php echo __("Users Connections"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Users_connections" class="tab-pane fade in active" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/UserConnections/View/Users_connections/index_body.php';
                            ?>
                        </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>