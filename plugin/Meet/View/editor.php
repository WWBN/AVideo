<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Meet");
$_page = new Page(array('Meet'));
$_page->loadBasicCSSAndJS();
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
<?php
$_page->print();
?>