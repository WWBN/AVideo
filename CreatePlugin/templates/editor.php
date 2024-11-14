<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("{pluginName}");
$_page = new Page(array('{pluginName}'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('{pluginName}') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("{pluginName}"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                {editorNavTabs}
            </ul>
            <div class="tab-content">
                {editorNavContent}
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>