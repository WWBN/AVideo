
<?php
require_once $global['systemRootPath'].'plugin/YouPHPTubePlugin.php';
// the live users plugin
if(!YouPHPTubePlugin::isEnabled("cf145581-7d5e-4bb6-8c12-48fc37c0630d")){
    return false;
}
?>
<span class="label label-primary"><i class="fa fa-user"></i> <b class="liveUsersOnline_<?php echo $streamName; ?>">0</b></span>
<span class="label label-default"><i class="fa fa-eye"></i> <b class="liveUsersViews_<?php echo $streamName; ?>">0</b></span>