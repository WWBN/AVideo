<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("SocialMediaPublisher");
$_page = new Page(array('SocialMediaPublisher'));
$_page->setExtraStyles(array('view/css/DataTables/datatables.min.css', 'view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css'));
$_page->setExtraScripts(array('view/css/DataTables/datatables.min.js', 'view/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js'));
?>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading"><?php echo __('SocialMediaPublisher') ?> 
            <div class="pull-right">
                <?php echo AVideoPlugin::getSwitchButton("SocialMediaPublisher"); ?>
            </div>
        </div>
        <div class="panel-body">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#Publisher_social_medias"><?php echo __("Publisher Social Medias"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Publisher_user_preferences"><?php echo __("Publisher User Preferences"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Publisher_video_publisher_logs"><?php echo __("Pblisher Video Publisher Logs"); ?></a></li>
<li class=""><a data-toggle="tab" href="#Publisher_schedule"><?php echo __("Publisher Schedule"); ?></a></li>
            </ul>
            <div class="tab-content">
                <div id="Publisher_social_medias" class="tab-pane fade in active" style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_social_medias/index_body.php';
                            ?>
                        </div>
<div id="Publisher_user_preferences" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_user_preferences/index_body.php';
                            ?>
                        </div>
<div id="Publisher_video_publisher_logs" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_video_publisher_logs/index_body.php';
                            ?>
                        </div>
<div id="Publisher_schedule" class="tab-pane fade " style="padding: 10px;">
                            <?php
                            include $global['systemRootPath'] . 'plugin/SocialMediaPublisher/View/Publisher_schedule/index_body.php';
                            ?>
                        </div>
            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>