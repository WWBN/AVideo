
<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-hdd"></i> <?php echo __("Devices Stream Info"); ?> (<?php echo $channelName; ?>)</div>
    <div class="panel-body" style="overflow: hidden;">
        <div class="form-group">
            <label for="server"><i class="fa fa-server"></i> <?php echo __("Server URL"); ?>:</label>
            <?php
            getInputCopyToClipboard('server', Live::getServer() . "?p=" . User::getUserPass());
            ?>
            <small class="label label-info"><i class="fa fa-warning"></i> <?php echo __("If you change your password the Server URL parameters will be changed too."); ?></small>
        </div>
        <div class="form-group">
            <label for="streamkey"><i class="fa fa-key"></i> <?php echo __("Stream name/key"); ?>:</label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a class="btn btn-default" href="<?php echo $global['webSiteRootURL']; ?>plugin/Live/?resetKey=1"><i class="fa fa-refresh"></i> <?php echo __("Reset Key"); ?></a>
                </span>
                <?php
                getInputCopyToClipboard('streamkey', $trasnmition['key']);
                ?>
            </div>
        </div>
        <div class="form-group">
            <label for="serverAndStreamkey"><i class="fa fa-key"></i> <?php echo __("Server URL"); ?> + <?php echo __("Stream name/key"); ?>:</label>
            <?php
            getInputCopyToClipboard('serverAndStreamkey', Live::getServer() . "?p=" . User::getUserPass() . "/" . $trasnmition['key']);
            ?>
            <span class="label label-warning"><i class="fa fa-warning"></i> <?php echo __("Keep Key Private, Anyone with key can broadcast on your account"); ?></span>
        </div>
    </div>
</div>