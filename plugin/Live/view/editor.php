<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
$_page = new Page(array('Live'));
$_page->loadBasicCSSAndJS();
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
                <li class=""><a data-toggle="tab" href="#Live_restreams_logs"><?php echo __("Live Restreams Logs"); ?></a></li>
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
                <div id="Live_restreams_logs" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Live/view/Live_restreams_logs/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>