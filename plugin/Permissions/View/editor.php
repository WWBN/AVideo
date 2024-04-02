<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Permissions");
$_page = new Page(array('Permissions'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Permissions') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("Permissions"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Users_groups_permissions"><?php echo __("Users Groups Permissions"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Users_groups_permissions" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Permissions/View/Users_groups_permissions/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>