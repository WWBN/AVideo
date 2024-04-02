<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("Scheduler");
$_page = new Page(array("Scheduler"));
$_page->loadBasicCSSAndJS();
?>

<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('Scheduler') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("Scheduler"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Scheduler_commands"><?php echo __("Scheduler Commands"); ?></a></li>
                <li class=""><a data-toggle="tab" href="#Emails_messages"><?php echo __("Emails Messages"); ?></a></li>
                <li class=""><a data-toggle="tab" href="#Email_to_user"><?php echo __("Email To User"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Scheduler_commands" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Scheduler/View/Scheduler_commands/index_body.php';
                    ?>
                </div>
                <div id="Emails_messages" class="tab-pane fade " style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Scheduler/View/Emails_messages/index_body.php';
                    ?>
                </div>
                <div id="Email_to_user" class="tab-pane fade " style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/Scheduler/View/Email_to_user/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>