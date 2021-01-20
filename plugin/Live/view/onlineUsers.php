
<?php
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
// the live users plugin
$lu = AVideoPlugin::getObjectDataIfEnabled("LiveUsers");
if (empty($lu) || $lu->doNotDisplayCounter) {
    return false;
}

if (empty($lu->doNotDisplayLiveCounter)) {
    $liveUsersOnlineClass = "liveUsersOnline_{$streamName} liveUsersOnline_{$streamName}_".Live::getAvailableLiveServer();
    ?>
    <span class="label label-primary"   data-toggle="tooltip" title="<?php echo __("Watching Now"); ?>" data-placement="bottom" ><i class="fa fa-eye"></i> <b class="total_on_same_live liveUsersOnline <?php echo $liveUsersOnlineClass; ?>">0</b></span>
    <?php
}
if (empty($lu->doNotDisplayTotal)) {
    $liveUsersViewsClass = "liveUsersViews_{$streamName} liveUsersViews_{$streamName}_".Live::getAvailableLiveServer();
    ?>
    <span class="label label-default"   data-toggle="tooltip" title="<?php echo __("Total Views"); ?>" data-placement="bottom" ><i class="fa fa-user"></i> <b class="liveUsersViews <?php echo $liveUsersViewsClass; ?>">0</b></span>
    <?php
}
?>