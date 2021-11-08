<div class="panel panel-default">
    <div class="panel-heading"><i class="fas fa-hdd"></i> <?php echo __("Advanced"); ?></div>
    <div class="panel-body" style="overflow: hidden;">
        <div class="form-group <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'key', 0.1); ?>">
            <label for="serverAndStreamkey"><i class="fa fa-key"></i> <?php echo __("Server URL"); ?> + <?php echo __("Stream name/key"); ?>:</label>
            <?php
            getInputCopyToClipboard('serverAndStreamkey', Live::getRTMPLink(User::getId()));
            ?>
        </div>
        <div class="form-group <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'key', 0.1); ?>">
            <label for="destinationApplication"><i class="fa fa-cog"></i> <?php echo __("Destination Application Name"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationApplication', Live::getDestinationApplicationName());
            ?>
        </div>
        <div class="form-group <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'key', 0.1); ?>">
            <label for="destinationHost"><i class="fa fa-cog"></i> <?php echo __("Destination Host"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationHost', Live::getDestinationHost());
            ?>
        </div>
        <div class="form-group <?php echo getCSSAnimationClassAndStyle('animate__flipInX', 'key', 0.1); ?>">
            <label for="destinationPort"><i class="fas fa-door-open"></i> <?php echo __("Destination Port"); ?>:</label>
            <?php
            getInputCopyToClipboard('destinationPort', Live::getDestinationPort());
            ?>
        </div>
    </div>
</div>