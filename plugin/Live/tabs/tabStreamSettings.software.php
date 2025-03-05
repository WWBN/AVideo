<div class="panel panel-default <?php echo getCSSAnimationClassAndStyle('animate__fadeInLeft', 'live'); ?>" id="RTMPSettings">
    <div class="panel-heading">
        <i class="fas fa-hdd"></i> <?php echo __("RTMP Settings"); ?> (<?php echo $channelName; ?>)
        <div class="pull-right">
            <?php
            echo getTourHelpButton('plugin/Live/tabs/help.json', 'btn btn-default btn-xs', 'Live Configuration Help');
            ?>
        </div>
    </div>
    <div class="panel-body" style="overflow: hidden;">

        <div class="form-group" id="ServerURL">
            <label for="server"><i class="fa fa-server"></i> <?php echo __("Server URL"); ?>:</label>
            <?php
            getInputCopyToClipboard('server', Live::getRTMPLinkWithOutKey(User::getId()));
            ?>
        </div>
        <div class="form-group" id="ServerName">
            <label for="streamkey"><i class="fa fa-key"></i> <?php echo __("Stream name/key"); ?>: </label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a class="btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?resetKey=1" data-toggle="tooltip" title="<?php echo __("This also reset the Chat and views counter"); ?>"><i class="fa fa-refresh"></i> <?php echo __("Reset Key"); ?></a>
                </span>
                <?php
                getInputCopyToClipboard('streamkey', $keyForStreamSettingTab);
                ?>
            </div>
        </div>
        <?php
        if (!empty($onliveApplicationsButtons)) {
        ?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo __('Active Livestreams'); ?>
                </div>
                <div class="panel-body myUsedKeys<?php echo $key; ?>">
                    <?php echo implode('', $onliveApplicationsButtons); ?>
                </div>
            </div>
        <?php
        }
        ?>

        <div class="form-group <?php echo getCSSAnimationClassAndStyle('animate__fadeInLeft', 'live'); ?>" id="ServerURLName">
            <label for="serverAndStreamkey"><i class="fa fa-key"></i> <?php echo __("Server URL"); ?> + <?php echo __("Stream name/key"); ?>:</label>
            <?php
            getInputCopyToClipboard('serverAndStreamkey', Live::getRTMPLink(User::getId()));
            ?>
        </div>
    </div>
    <div class="panel-footer">
        <!-- Insert the recommended settings panel here -->
        <?php include __DIR__ . '/recommended_stream_settings.php'; ?>
    </div>
</div>
