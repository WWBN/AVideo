<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("PayPalYPT");
$_page = new Page(array('PayPal'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('PayPalYPT') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("PayPalYPT"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#PayPalYPT_log"><?php echo __("PayPalYPT Log"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="PayPalYPT_log" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/PayPalYPT/View/PayPalYPT_log/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>