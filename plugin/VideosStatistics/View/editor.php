<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("VideosStatistics");
$_page = new Page(array('VideosStatistics'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('VideosStatistics') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("VideosStatistics"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Statistics"><?php echo __("Statistics"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Statistics" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/VideosStatistics/View/Statistics/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>