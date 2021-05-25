<?php
$objLive = AVideoPlugin::getDataObject("Live");
//Live::deleteStatsCache();
if ($objLive->allowMultipleLivesPerUser) {
    $onliveApplications = Live::getLivesOnlineFromKey($key);
    foreach ($onliveApplications as $value) {
        if (empty($value['key'])) {
            continue;
        }
        if (preg_match('/' . $trasnmition['key'] . '/', $value['key'])) {
            $onliveApplications[] = '<a class="btn btn-default btn-block live_' . $value['live_servers_id'] . '_' . $value['key'] . '" href="' . $value['href'] . '" target="_blank"><span class="label label-danger liveNow faa-flash faa-slow animated">' . __('LIVE NOW') . '</span> ' . $value['title'] . '</a>';
        }
    }
}
$key = $liveStreamObject->getKeyWithIndex(true);
?>
<style>
    #streamkey{
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-hdd"></i> <?php echo __("Devices Stream Info"); ?> (<?php echo $channelName; ?>)</div>
    <div class="panel-body" style="overflow: hidden;">
        <div class="form-group">
            <label for="server"><i class="fa fa-server"></i> <?php echo __("Server URL"); ?>:</label>
            <?php
            getInputCopyToClipboard('server', Live::getRTMPLinkWithOutKey(User::getId()));
            ?>
            <small class="label label-info"><i class="fa fa-warning"></i> <?php echo __("If you change your password the Server URL parameters will be changed too."); ?></small>
            <span class="label label-warning"><i class="fa fa-warning"></i> <?php echo __("Keep Key Private, Anyone with key can broadcast on your account"); ?></span>
        </div>
        <div class="form-group">
            <label for="streamkey"><i class="fa fa-key"></i> <?php echo __("Stream name/key"); ?>: </label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a class="btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?resetKey=1" data-toggle="tooltip" title="<?php echo __("This also reset the Chat and views counter"); ?>"><i class="fa fa-refresh"></i> <?php echo __("Reset Key"); ?></a>
                </span>
                <?php
                getInputCopyToClipboard('streamkey', $key);
                ?>
            </div>
        </div>
        <?php
        if (!empty($onliveApplications)) {
            ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __('Active Livestreams'); ?>
                </div>
                <div class="panel-body myUsedKeys<?php echo $key; ?>">
                    <?php
                    echo implode('', $onliveApplications);
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-hdd"></i> <?php echo __("Advanced"); ?></div>
    <div class="panel-body" style="overflow: hidden;">
        <div class="form-group">
            <label for="serverAndStreamkey"><i class="fa fa-key"></i> <?php echo __("Server URL"); ?> + <?php echo __("Stream name/key"); ?>:</label>
            <?php
            getInputCopyToClipboard('serverAndStreamkey', Live::getRTMPLink(User::getId()));
            ?>
        </div>
        <div class="form-group">
            <label for="destinationApplication"><i class="fa fa-cog"></i> <?php echo __("Destination Application Name"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationApplication', Live::getDestinationApplicationName());
            ?>
        </div>
        <div class="form-group">
            <label for="destinationHost"><i class="fa fa-cog"></i> <?php echo __("Destination Host"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationHost', Live::getDestinationHost());
            ?>
        </div>
        <div class="form-group">
            <label for="destinationPort"><i class="fas fa-door-open"></i> <?php echo __("Destination Port"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationPort', Live::getDestinationPort());
            ?>
        </div>
    </div>
</div>