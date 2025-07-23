<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("AuthorizeNet");
$_page = new Page(array('AuthorizeNet'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('AuthorizeNet') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("AuthorizeNet"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Anet_webhook_log"><?php echo __("anet_webhook_log"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Anet_webhook_log" class="tab-pane fade in active" style="padding: 10px;">
                            <?php include $global['systemRootPath'] . 'plugin/AuthorizeNet/View/Anet_webhook_log/index_body.php'; ?>
                        </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>