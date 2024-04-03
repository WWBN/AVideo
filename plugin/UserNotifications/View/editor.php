<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("UserNotifications");
$_page = new Page(array('UserNotifications'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('UserNotifications') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("UserNotifications"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#User_notifications"><?php echo __("User Notifications"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="User_notifications" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/UserNotifications/View/User_notifications/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>