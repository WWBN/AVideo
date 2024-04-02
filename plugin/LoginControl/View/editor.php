<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("LoginControl");
$_page = new Page(array('Login Control'));
$_page->loadBasicCSSAndJS();
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('LoginControl') ?>
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("LoginControl"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#logincontrol_history"><?php echo __("Users Login History"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="logincontrol_history" class="tab-pane fade in active" style="padding: 10px;">
                    <?php
                    include $global['systemRootPath'] . 'plugin/LoginControl/View/Users_login_history/index_body.php';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>