<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("PlayLists");
$_page = new Page(array("PlayLists"));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('PlayLists') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("PlayLists"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Playlists_schedules"><?php echo __("Playlists Schedules"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Playlists_schedules" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/PlayLists/View/Playlists_schedules/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>