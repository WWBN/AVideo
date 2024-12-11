<?php
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (empty($_REQUEST['live_schedule_id'])) {
    forbiddenPage('live_schedule_id cannot be empty');
}

$live = AVideoPlugin::loadPluginIfEnabled('Live');
if (empty($live)) {
    forbiddenPage('Live plugin is disabled');
}

$ls = new Live_schedule($_REQUEST['live_schedule_id']);
$liveImg = Live_schedule::getPosterURL($_REQUEST['live_schedule_id'], 0);

$liveInfo = Live::getInfo($ls->getKey(), $ls->getLive_servers_id());
$_page = new Page(array('Remind me'));
//$_page->setIncludeNavbar(false);
?>
<style>
    .schedulePoster {
        max-width: 20vw;
        max-height: 10vh;
        padding: 0 10px 0 0;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="pull-left">
                <img src="<?php echo $liveImg; ?>" class="img img-responsive schedulePoster">
            </div>
            <h2>
                <?php
                echo __('Remind me');
                ?>
            </h2>
            <h3>
                <?php
                echo $ls->getTitle();
                ?>
            </h3>
        </div>
        <div class="panel-body" style="padding: 10px;">
            <?php
            echo Live::getScheduleReminderOptions($_REQUEST['live_schedule_id']);
            ?>
        </div>
        <div class="panel-footer">
            <i class="far fa-clock"></i>
            <?php
            echo $liveInfo['displayTime'];
            ?>
        </div>
    </div>
</div>
<?php
$_page->print();
?>