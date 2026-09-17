<?php
// Included only by the enabled video's watch-page footer hook.
if (empty($embedUrl)) {
    return;
}
?>
<link rel="stylesheet" href="<?php echo getURL('plugin/AI/View/companionWidget.css'); ?>">
<div class="companion-widget" id="companionChatWidget">
    <section class="companion-widget-panel panel panel-default" id="companionChatPanel" role="dialog" aria-label="<?php echo htmlspecialchars(__('Companion chat'), ENT_QUOTES, 'UTF-8'); ?>" hidden>
        <div class="companion-widget-bar panel-heading">
            <span><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
            <div class="companion-widget-actions">
                <button type="button" class="btn btn-default btn-sm companion-widget-expand" aria-label="<?php echo htmlspecialchars(__('Expand'), ENT_QUOTES, 'UTF-8'); ?>" aria-pressed="false"><i class="fas fa-expand" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-default btn-sm companion-widget-close" aria-label="<?php echo htmlspecialchars(__('Close chat'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fas fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        <iframe class="companion-widget-frame" data-src="<?php echo htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(__('Companion chat'), ENT_QUOTES, 'UTF-8'); ?>" referrerpolicy="strict-origin-when-cross-origin"></iframe>
    </section>
    <button type="button" class="companion-widget-launcher btn btn-circle btn-default btn-lg" aria-controls="companionChatPanel" aria-expanded="false" aria-label="<?php echo htmlspecialchars(__('Open chat'), ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars(__('AI Chat'), ENT_QUOTES, 'UTF-8'); ?>">
        <i class="fas fa-comment-dots" aria-hidden="true"></i>
    </button>
</div>
<script src="<?php echo getURL('plugin/AI/View/companionWidget.js'); ?>"></script>
