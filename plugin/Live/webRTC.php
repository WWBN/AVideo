<?php
$iframeURL = 'https://webrtc.ca1.ypt.me/player/';
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));
?>
<span class=" pull-right">
    <button class="btn btn-default btn-xs" id="startWebcam" onclick="startWebcamNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <span class="hidden-sm hidden-xs"><?php echo __("Webcam"); ?></span>
    </button>
</span>
<script>
    function startWebcamNow() {
        showWebcam();
        $('#divWebcamIFrame iframe').attr('src', '<?php echo $iframeURL; ?>');
    }

    function showWebcam() {
        $('#mainVideo').slideUp();
        $('#divMeetToIFrame').slideUp();
        $('#divWebcamIFrame').slideDown();
        player.pause();
    }

    function hideWebcam() {
        $('#mainVideo').slideDown();
        $('#divMeetToIFrame').slideUp();
        $('#divWebcamIFrame').slideUp();
    }

    window.addEventListener('message', event => {
        if (event.data.startLiveRestream) {
            startLiveRestream(event.data.m3u8);
        }
    });

    function startLiveRestream(m3u8) {
        //console.log('WebRTCLiveCam: startLive');
        modal.showPleaseWait();
        $.ajax({
            url: webSiteRootURL + '/plugin/Live/webRTCToLive.json.php',
            method: 'POST',
            data: {
                'm3u8': m3u8,
                'live_servers_id': '<?php echo Live::getCurrentLiveServersId(); ?>'
            },
            success: function (response) {
                if (response.error) {
                    avideoAlertError(response.msg);
                    stopStreaming();
                } else {
                    avideoToastSuccess(response.msg);
                    $('body').addClass('webRTCBtnStarted');
                }
                modal.hidePleaseWait();
            }
        });
    }

    $(document).ready(function () {
    });
</script>