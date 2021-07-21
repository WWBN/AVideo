<?php
$iframeURL = 'https://webrtc.ca1.ypt.me/player/';
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));
?>
<span class=" pull-right" style="margin-left: 5px;">
    <button class="btn btn-danger btn-xs showOnWebRTC" id="webRTCDisconnect" style="display: none;" onclick="webRTCDisconnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <span class="hidden-sm hidden-xs"><?php echo __("Stop"); ?></span>
    </button>
    <button class="btn btn-success btn-xs showOnWebRTC" id="webRTCConnect" style="display: none;" onclick="webRTCConnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <span class="hidden-sm hidden-xs"><?php echo __("Go Live"); ?></span>
    </button>
    <button class="btn btn-primary btn-xs showOnWebRTC" style="display: none;" onclick="webRTCConfiguration();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Configuration"); ?>">
        <i class="fas fa-cog"></i> <span class="hidden-sm hidden-xs"><?php echo __("Configuration"); ?></span>
    </button>
    <button class="btn btn-default btn-xs" id="startWebcam" onclick="toogleWebcam();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <span class="hidden-sm hidden-xs"><?php echo __("Webcam"); ?></span>
    </button>
</span>
<script src="<?php echo getCDN(); ?>plugin/Live/webRTC.js" type="text/javascript"></script>
<script>
        var updateControlStatusLastState;
        function toogleWebcam() {
            updateControlStatusLastState = null;
            isVisible = $('#divWebcamIFrame').is(":visible");
            if (isVisible) {
                hideWebcam();
            } else {
                showWebcam();
            }
        }

        function showWebcam() {
            $('#mainVideo').hide();
            $('#divMeetToIFrame').hide();
            $('#divWebcamIFrame').show();
            $('.showOnWebRTC').show();
            $('#divWebcamIFrame iframe').attr('src', '<?php echo $iframeURL; ?>');
            if (typeof stopMeetNow == 'function') {
                stopMeetNow();
            }
            player.pause();
        }

        function hideWebcam() {
            $('#mainVideo').show();
            $('#divMeetToIFrame').hide();
            $('#divWebcamIFrame').hide();
            $('.showOnWebRTC').hide();
            $('#divWebcamIFrame iframe').attr('src', 'about:blank');
        }
        function updateControlStatus() {
            isVisible = $('#divWebcamIFrame').is(":visible");
            if (isVisible) {
                var hasclass = $('.liveOnlineLabel').hasClass('label-danger');
                if (updateControlStatusLastState === hasclass) {
                    return false;
                }
                updateControlStatusLastState = hasclass;
                if (hasclass) {
                    $('#webRTCDisconnect').hide();
                    $('#webRTCConnect').show();
                } else {
                    $('#webRTCDisconnect').show();
                    $('#webRTCConnect').hide();
                }
            } else {
                $('.showOnWebRTC').slideUp();
            }
        }

        $(document).ready(function () {
            setInterval(function () {
                updateControlStatus();
            }, 1000);
        });
</script>