<?php
require_once __DIR__ . '/../../videos/configuration.php';
AVideoPlugin::loadPlugin("Live");
$_GET['avideoIframe'] = 1;
$_page = new Page(array('Webcam'), 'quickGoLiveMode');
?>
<style>
    body {
        overflow: hidden;
        background: #000;
    }

    video {
        position: fixed;
        top: 0;
        left: 0;
    }

    /* Chat overlay: bounded bottom-left column, clear of the right rail and bottom action bar */
    .transparent-iframe {
        top: auto !important;
        left: max(12px, env(safe-area-inset-left)) !important;
        bottom: max(88px, calc(80px + env(safe-area-inset-bottom))) !important;
        width: min(calc(100vw - 86px), 420px) !important;
        height: min(58vh, 500px) !important;
        border-radius: 18px;
        overflow: hidden;
        z-index: 5 !important;
    }

    /* Controls overlay: fullscreen positioning context, transparent to clicks except its buttons/card */
    #webcamMediaControls {
        position: fixed;
        inset: 0;
        z-index: 10;
        pointer-events: none;
    }

    #mediaSelector {
        pointer-events: auto;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: max(100px, calc(92px + env(safe-area-inset-bottom)));
        width: min(92vw, 480px);
        max-height: 40vh;
        overflow-y: auto;
        margin: 0 !important;
        padding: 14px;
        border-radius: 18px;
        background: rgba(20, 20, 20, .72);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, .35);
        /* Flex layout driven by the card's own width, not the viewport, since Bootstrap's
           col-sm-3/col-xs-6 breakpoints react to viewport width and don't fit this fixed-width card. */
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    #mediaSelector > div {
        float: none;
        width: auto;
        flex: 1 1 45%;
        min-width: 120px;
        padding: 0;
    }

    #mediaSelector select,
    #mediaSelector .input-group-addon {
        background: rgba(255, 255, 255, .1);
        color: #fff;
        border-color: rgba(255, 255, 255, .3);
    }

    #mediaSelector select option {
        color: #000;
        background: #fff;
    }

    #mediaSelector .btn {
        border-radius: 999px;
        white-space: normal;
    }

    /* Scoped to .text-center (panel.buttons.php's button-row wrappers) so this doesn't also
       match the top-bar status indicators, which reuse the same showWhenIsLive/NotLive classes. */
    .text-center.showWhenIsNotLive,
    .text-center.showWhenIsLive {
        pointer-events: none;
        position: absolute;
        inset: 0;
        margin: 0;
    }

    .showWhenIsNotLive .oval-menu,
    .showWhenIsLive .oval-menu {
        pointer-events: auto;
        position: absolute;
        margin: 0;
        height: 50px;
        min-width: 50px;
        border: none;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .4);
    }

    /* Main action: Start / Stop, bottom-center like TikTok/Instagram's primary live button */
    #startLive, #stopLive {
        left: 50%;
        transform: translateX(-50%);
        bottom: max(20px, env(safe-area-inset-bottom));
        min-width: 170px;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: .03em;
    }

    /* Right-side vertical rail: camera on/off + device settings.
       Fixed width+height (not min-width) guarantees a perfect circle regardless of icon content. */
    #stopWebRTC, #startWebRTC, #toggleMediaSelectorButton {
        width: 50px;
        height: 50px;
        padding: 0;
        overflow: hidden;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #stopWebRTC i, #startWebRTC i, #toggleMediaSelectorButton i {
        margin: 0;
        font-size: 20px;
        line-height: 1;
    }

    #stopWebRTC, #startWebRTC {
        right: max(16px, env(safe-area-inset-right));
        bottom: max(90px, calc(82px + env(safe-area-inset-bottom)));
    }

    /* The camera-off icon overlays a "slash" absolutely; anchor it to its own small
       wrapper instead of the nearest positioned ancestor (this button, now position:absolute). */
    #stopWebRTC > div {
        position: relative;
        display: inline-flex;
    }

    #toggleMediaSelectorButton {
        right: max(16px, env(safe-area-inset-right));
        bottom: max(152px, calc(144px + env(safe-area-inset-bottom)));
        background: rgba(255, 255, 255, .15) !important;
        border: 1px solid rgba(255, 255, 255, .3) !important;
        color: #fff !important;
    }
</style>

<?php
include __DIR__ . '/video.php';
?>
<div id="webcamMediaControls" class="showWhenWebRTCIsConnected">
    <?php
    include __DIR__ . '/panel.medias.php';
    include __DIR__ . '/panel.buttons.php';
    ?>
</div>
<div id="webcamMediaControlsMessage" class="showWhenWebRTCIsNotConnected text-center" style="display: none;">
    <div class="row">
        <div class="col-sm-3">
        </div>
        <div class="col-sm-6">
            <div class="alert alert-danger">
                <div class="fa-3x">
                    <i class="fa-solid fa-triangle-exclamation fa-fade"></i>
                </div>
                <strong>Error:</strong> Unable to connect to the Webcam server.<br>
                <span>Please verify the server status and resolve any issues.</span>
            </div>
        </div>
        <div class="col-sm-3">
        </div>
    </div>
</div>

<script>
    // Quick Go Live entry point: preview only, publishing requires an explicit "Confirm setup" + "Start".
    // body.quickGoLiveMode is set server-side (see the Page() constructor above).
    $(document).ready(async function() {
        $('#startLive')
            .html('<i class="fa fa-play"></i> ' + __('Start'))
            .attr('title', '<?php echo __('Click here to start your live broadcast'); ?>');

        // Show the device selectors right away so the user can review/change them before starting.
        $('#mediaSelector').show();

        var savedDevices = await (window.webrtcSourcesReadyPromise || Promise.resolve(null));
        var started = await startWebRTC(savedDevices ? {
            videoDeviceId: savedDevices.videoDeviceId,
            audioDeviceId: savedDevices.audioDeviceId
        } : {});

        // A previously confirmed device selection is still valid: skip straight to preview -> Start.
        if (started && savedDevices) {
            markWebrtcSetupConfirmed();
            $('#mediaSelector').hide();
        }
    });

    // Release camera/mic and stop publishing when the Quick Go Live modal is closed.
    function quickGoLiveCleanup() {
        try {
            if (typeof isLive !== 'undefined' && isLive && typeof rtmpURLEncrypted !== 'undefined') {
                stopWebcamLive(rtmpURLEncrypted);
            }
            if (typeof stopStreamToServer === 'function') {
                stopStreamToServer();
            }
            if (typeof stopWebRTC === 'function') {
                stopWebRTC();
            }
        } catch (e) {
            console.warn('Error during Quick Go Live cleanup:', e);
        }
    }
    window.addEventListener('pagehide', quickGoLiveCleanup);
    window.addEventListener('beforeunload', quickGoLiveCleanup);
</script>
<?php

$_page->print();
?>
