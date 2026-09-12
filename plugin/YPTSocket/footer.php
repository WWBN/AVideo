<?php
global $global;
if (isConfirmationPage()) {
    echo '<!-- isConfirmationPage socket_info_container -->';
    return false;
}
if (isBot()) {
    echo '<!-- isBot socket_info_container -->';
    return false;
}
$refl = new ReflectionClass('SocketMessageType');
$obj = AVideoPlugin::getDataObjectIfEnabled('YPTSocket');
if (!empty($obj->debugAllUsersSocket) || (User::isAdmin() && !empty($obj->debugSocket))) {
?>
    <link rel="stylesheet" href="<?php echo getURL('plugin/YPTSocket/socketInfo.css'); ?>">
    <aside id="socket_info_container" class="socket_info socketMinimized socket-inspector hidden" aria-label="<?php echo __('Socket'); ?>">
        <section id="socketInfoPanel" class="socket-inspector-panel panel panel-default" aria-labelledby="socketInfoTitle" hidden>
            <header class="panel-heading socket-inspector-heading" title="<?php echo __('Drag to move'); ?>">
                <strong id="socketInfoTitle"><?php echo __('Socket'); ?></strong>
                <button type="button" class="btn btn-default btn-xs" id="socketInfoClose" aria-label="<?php echo __('Close'); ?>" title="<?php echo __('Close'); ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
            </header>
            <div class="panel-body socket-inspector-body">
                <p class="small"><?php echo __('Connection used to receive chat messages and notifications without refreshing the page.'); ?></p>
                <p class="socket-inspector-status" role="status" aria-live="polite"
                   data-connected="<?php echo __('Connected'); ?>"
                   data-disconnected="<?php echo __('Disconnected'); ?>"
                   data-loading="<?php echo __('Connecting'); ?>"><?php echo __('Connecting'); ?></p>
                <p class="small text-muted socket-info-connected"><?php echo __('Real-time updates are active.'); ?></p>
                <p class="small text-muted socket-info-loading"><?php echo __('Connecting to real-time updates. You can continue browsing.'); ?></p>
                <p class="small text-muted socket-info-disconnected"><?php echo __('Real-time updates are paused. Reconnection is automatic; you can continue browsing.'); ?></p>
                <div class="socket-inspector-metrics">
                    <div class="well well-sm"><strong class="total_devices_online">—</strong><span><?php echo __('Online users (by device)'); ?></span></div>
                    <div class="well well-sm"><strong class="total_users_online">—</strong><span><?php echo __('Active connections'); ?></span></div>
                </div>
                <p class="small text-muted"><?php echo __('Site-wide users and visitors, counted by device. Multiple tabs on the same device count once per user.'); ?></p>
                <dl class="socket-inspector-facts">
                    <div><dt><?php echo __('Connected for'); ?></dt><dd id="socketInfoDuration">—</dd></div>
                    <div><dt><?php echo __('Last server update'); ?></dt><dd id="socketInfoLastUpdate">—</dd></div>
                    <div><dt><?php echo __('Reconnections on this page'); ?></dt><dd id="socketInfoReconnects">0</dd></div>
                </dl>
                <p class="small text-muted socket-info-disconnected"><?php echo __('Displayed totals are from the last received update.'); ?></p>
                <details class="socket-inspector-details">
                    <summary><?php echo __('Technical details'); ?></summary>
                    <dl class="socket-inspector-facts">
                        <div><dt><?php echo __('Server version'); ?></dt><dd class="webSocketServerVersion">—</dd></div>
                        <div><dt><?php echo __('Server memory'); ?></dt><dd class="socket_mem">—</dd></div>
                        <div><dt><?php echo __('Transport'); ?></dt><dd id="socketInfoTransport">—</dd></div>
                    </dl>
                    <p class="small text-muted"><?php echo __('Memory is reported by the socket server. These values do not measure video quality or your internet speed.'); ?></p>
                </details>
                <details class="socket-inspector-details">
                    <summary><?php echo __('Online activity'); ?></summary>
                    <p class="small text-muted"><?php echo __('Expand a user to see available pages. Chat and background connections are omitted from this list.'); ?></p>
                    <div id="socketUsersURI"></div>
                    <p class="small text-muted socket-inspector-empty"><?php echo __('No page activity to display yet.'); ?></p>
                </details>
                <div class="socket-inspector-actions socket-info-disconnected">
                    <button type="button" class="btn btn-default btn-sm" id="socketInfoRetry"><?php echo __('Reconnect'); ?></button>
                </div>
                <?php if (User::isAdmin()) { ?>
                    <details class="socket-inspector-details">
                        <summary><?php echo __('Server administration'); ?></summary>
                        <p class="small text-muted"><?php echo __('Restarting disconnects everyone briefly.'); ?></p>
                        <div class="socket-inspector-actions">
                            <button type="button" class="btn btn-default btn-sm" id="socketInfoCopyCommand" data-command="<?php echo htmlspecialchars('sudo ' . YPTSocket::getStartServerCommand(), ENT_QUOTES, 'UTF-8'); ?>"><?php echo __('Copy start command'); ?></button>
                            <button type="button" class="btn btn-danger btn-sm" id="socketInfoRestart" data-confirm="<?php echo __('Restart the socket server? All connected users will reconnect.'); ?>"><i class="fas fa-power-off" aria-hidden="true"></i> <?php echo __('Restart server'); ?></button>
                        </div>
                    </details>
                <?php } ?>
            </div>
        </section>
        <button type="button" id="socketInfoToggle" class="socket-inspector-toggle btn btn-default" aria-expanded="false" aria-controls="socketInfoPanel" title="<?php echo __('Chat and notification connection. Click for details or drag to move.'); ?>">
            <i class="fas fa-plug socket-inspector-indicator" aria-hidden="true"></i>
            <span class="socket-inspector-caption">
                <span class="socket-inspector-name"><?php echo __('Socket'); ?></span>
                <span class="socket-inspector-summary">
                    <span class="socket-inspector-label"><?php echo __('Connecting'); ?></span>
                    <span id="socketInfoOnline" class="socket-inspector-online" title="<?php echo __('Site-wide users and visitors, counted by device. Multiple tabs on the same device count once per user.'); ?>" hidden>
                        <span aria-hidden="true"> &middot; <i class="fas fa-users"></i> </span><?php echo __('Online'); ?>: <strong id="socketInfoOnlineCount">—</strong>
                    </span>
                </span>
            </span>
            <i class="fas fa-chevron-up socket-inspector-chevron" aria-hidden="true"></i>
        </button>
    </aside>
    <script src="<?php echo getURL('plugin/YPTSocket/socketInfo.js'); ?>"></script>
<?php
}
?>
<script>
    var webSocketSelfURI = '<?php echo getSelfURI(); ?>';
    var webSocketVideos_id = '<?php echo getVideos_id(); ?>';
    var webSocketLiveKey = '<?php echo json_encode(isLive()); ?>';
    var webSocketServerVersion = '<?php echo YPTSocket::getServerVersion(); ?>';
    var schedulerIsActive = <?php echo class_exists('Scheduler') && Scheduler::isActive() ? 1 : 0; ?>;
    var webSocketToken = '';
    var webSocketURL = '';
    var webSocketTypes = <?php echo json_encode($refl->getConstants()); ?>;

    $(document).ready(function() {
        <?php
        if (!isEmbed()) {
        ?>
            if (!inIframe()) {
                $('#socket_info_container').removeClass('hidden');
            }
        <?php
        }
        ?>
    });

    function onUserSocketConnect(response) {
        try {
            <?php echo AVideoPlugin::onUserSocketConnect(); ?>
        } catch (e) {
            console.log('onUserSocketConnect:error', e.message);
        }
    }

    function onUserSocketDisconnect(response) {
        try {
            <?php echo AVideoPlugin::onUserSocketDisconnect(); ?>
        } catch (e) {
            console.log('onUserSocketDisconnect:error', e.message);
        }
    }
</script>
<script src="<?php echo getURL('node_modules/socket.io-client/dist/socket.io.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo getURL('plugin/YPTSocket/script.js'); ?>" type="text/javascript"></script>
