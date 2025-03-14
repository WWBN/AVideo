<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("BTCPayments");
$_page = new Page(array('BTCPayments'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('BTCPayments') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("BTCPayments"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Btc_invoices"><?php echo __("btc_invoices"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Btc_payments"><?php echo __("btc_payments"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Btc_invoices" class="tab-pane fade in active" style="padding: 10px;">
                            <?php include $global['systemRootPath'] . 'plugin/BTCPayments/View/Btc_invoices/index_body.php'; ?>
                        </div>
<div id="Btc_payments" class="tab-pane fade " style="padding: 10px;">
                            <?php include $global['systemRootPath'] . 'plugin/BTCPayments/View/Btc_payments/index_body.php'; ?>
                        </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>