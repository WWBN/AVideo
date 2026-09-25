<section class="webrtc-studio panel panel-default" aria-label="<?php echo __('Live studio'); ?>">
    <div class="panel-heading webrtc-heading">
        <strong><?php echo __('Live studio'); ?></strong>
    </div>
    <div class="panel-body">
        <?php include __DIR__ . '/video.php'; ?>
        <?php include __DIR__ . '/panel.buttons.php'; ?>
        <div class="webrtc-sidebar">
            <?php include __DIR__ . '/panel.medias.php'; ?>
            <details id="webrtcChatPanel" class="webrtc-chat">
                <summary><?php echo __('Chat'); ?></summary>
                <iframe id="webrtcChat" title="<?php echo __('Live chat'); ?>" src="<?php echo $global['webSiteRootURL']; ?>plugin/MobileYPT/index.php?key=<?php echo $key; ?>&live_index=<?php echo $forceIndex; ?>"></iframe>
            </details>
        </div>
    </div>
</section>
