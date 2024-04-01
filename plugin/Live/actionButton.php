<?php

$isLive = isLive();
if (!empty($isLive) && Live::canManageLiveFromLiveKey($isLive['key'], User::getId())) {
?>
    <button class="btn btn-default no-outline" onclick="copyToClipboard($('#mainVideo > video > source').attr('src'));" data-toggle="tooltip" title="<?php echo __('Copy m3u8 link'); ?>">
        <i class="fa-solid fa-copy"></i>
        <span class=""><?php echo __('Copy m3u8 link'); ?></span>
    </button>
<?php
}
if (!empty($isLive['live_schedule'])) {
?>
    <button class="btn btn-default no-outline" onclick="avideoModalIframeSmall(webSiteRootURL+'plugin/Live/remindMe.php?live_schedule_id=<?php echo $isLive['live_schedule']; ?>');">
        <i class="fas fa-bell"></i>
        <?php echo  __('Remind Me'); ?>
    </button>
<?php
}
?>