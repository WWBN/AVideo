<?php
$audienceLive = isLive();
if (empty($audienceLive['key']) || isBot()) {
    return;
}
$audienceServerId = (int) ($audienceLive['live_servers_id'] ?? 0);
?>
<script>
$(function () {
    if (window.avideoLiveAudienceStarted) { return; }
    window.avideoLiveAudienceStarted = true;
    function heartbeat() {
        // Count playing viewers, including background playback, rather than idle watch pages.
        if (typeof player === 'undefined' || !player || typeof player.paused !== 'function' || player.paused() || player.ended()) {
            window.setTimeout(heartbeat, 15000);
            return;
        }
        $.ajax({
            url: webSiteRootURL + 'plugin/Live/audience.json.php',
            type: 'POST', dataType: 'json', timeout: 10000,
            data: {
                key: <?php echo json_encode($audienceLive['key'], JSON_HEX_TAG | JSON_HEX_AMP); ?>,
                live_servers_id: <?php echo $audienceServerId; ?>,
                globalToken: <?php echo json_encode(getToken(43200, 'live-audience:' . $audienceLive['key'] . ':' . $audienceServerId)); ?>
            },
            complete: function () { window.setTimeout(heartbeat, 15000); }
        });
    }
    heartbeat();
});
</script>
