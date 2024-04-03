<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("VideoTags");
$_page = new Page(array('VideoTags'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('VideoTags') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("VideoTags"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Tags_subscriptions"><?php echo __("Tags Subscriptions"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Tags_subscriptions" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/VideoTags/View/Tags_subscriptions/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>