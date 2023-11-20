<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("AI");
$_page = new Page(array('AI'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('AI') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("AI"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Ai_responses"><?php echo __("Ai Responses"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Ai_metatags_responses"><?php echo __("Ai Metatags Responses"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Ai_transcribe_responses"><?php echo __("Ai Transcribe Responses"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Ai_responses" class="tab-pane fade in active" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/View/Ai_responses/index_body.php';
                            ?>
                        </div>
<div id="Ai_metatags_responses" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/View/Ai_metatags_responses/index_body.php';
                            ?>
                        </div>
<div id="Ai_transcribe_responses" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/AI/View/Ai_transcribe_responses/index_body.php';
                            ?>
                        </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>