<?php
// Included only by the enabled video's watch-page footer hook.
if (empty($embedUrl)) {
    return;
}
if (!isset($configUrl)) {
    $configUrl = '';
}
?>
<link rel="stylesheet" href="<?php echo getURL('plugin/AI/View/companionWidget.css'); ?>">
<div class="companion-widget" id="companionChatWidget" data-config-url="<?php echo htmlspecialchars($configUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <section class="companion-widget-panel panel panel-default" id="companionChatPanel" role="dialog" aria-label="<?php echo htmlspecialchars(__('Companion chat'), ENT_QUOTES, 'UTF-8'); ?>" hidden>
        <div class="companion-widget-bar panel-heading">
            <span class="companion-widget-title"><i class="fas fa-comment-dots" aria-hidden="true"></i><span class="companion-widget-title-text"></span></span>
            <div class="companion-widget-actions">
                <button type="button" class="btn btn-default btn-sm companion-widget-expand" aria-label="<?php echo htmlspecialchars(__('Expand'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false"><i class="fas fa-expand" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-default btn-sm companion-widget-close" aria-label="<?php echo htmlspecialchars(__('Close chat'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        <iframe class="companion-widget-frame" data-src="<?php echo htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(__('Companion chat'), ENT_QUOTES, 'UTF-8'); ?>" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </section>
    <div class="companion-widget-dock">
        <!-- Invitation bubble: shows a few seconds after load, until the viewer opens the chat or dismisses it. -->
        <div class="companion-widget-teaser" hidden data-default-message="<?php echo htmlspecialchars(__('Any questions about this video? I\'m here to help 🙂', true), ENT_QUOTES, 'UTF-8'); ?>">
            <button type="button" class="companion-widget-teaser-text"></button>
            <button type="button" class="companion-widget-teaser-dismiss" aria-label="<?php echo htmlspecialchars(__('Dismiss'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <button type="button" class="companion-widget-launcher btn btn-circle btn-default btn-lg" aria-controls="companionChatPanel" aria-expanded="false" aria-label="<?php echo htmlspecialchars(__('Open chat'), ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(__('AI Chat'), ENT_QUOTES, 'UTF-8'); ?>" data-label-open="<?php echo htmlspecialchars(__('Open chat'), ENT_QUOTES, 'UTF-8'); ?>" data-label-close="<?php echo htmlspecialchars(__('Close chat'), ENT_QUOTES, 'UTF-8'); ?>">
            <i class="fas fa-comment-dots companion-widget-launcher-icon" aria-hidden="true"></i>
            <img class="companion-widget-launcher-photo" alt="" hidden>
            <span class="companion-widget-launcher-status" aria-hidden="true" hidden></span>
            <span class="companion-widget-launcher-toggle" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
        </button>
    </div>
</div>
<script src="<?php echo getURL('plugin/AI/View/companionWidget.js'); ?>"></script>
