
<div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-share"></i> <?php echo __("Share Info"); ?> (<?php echo $channelName; ?>)</div>
    <div class="panel-body">          
        <div class="form-group">
            <label for="playerURL"><i class="fa fa-play-circle"></i> <?php echo __("Player URL"); ?>:</label>
            <?php
            getInputCopyToClipboard('playerURL', $liveStreamObject->getM3U8());
            ?>
        </div>       
        <div class="form-group">
            <label for="avideoURL"><i class="fa fa-circle"></i> <?php echo __("Live URL"); ?>:</label>
            <?php
            getInputCopyToClipboard('avideoURL', $liveStreamObject->getURL());
            ?>
        </div>   
        <div class="form-group">
            <label for="embedStream"><i class="fa fa-code"></i> <?php echo __("Embed Stream"); ?>:</label>
            <?php
            getInputCopyToClipboard('embedStream', '<iframe width="640" height="480" style="max-width: 100%;max-height: 100%;" src="' . $liveStreamObject->getURLEmbed() . '" frameborder="0" allowfullscreen="allowfullscreen" ></iframe>');
            ?>
        </div>
    </div>
</div>