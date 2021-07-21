<?php
$iframeURL = 'https://webrtc.ca1.ypt.me/player/';
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));
?>
<span class=" pull-right">
    <button class="btn btn-danger btn-xs showOnWebRTC" id="webRTCDisconnect" style="display: none;" onclick="webRTCDisconnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-success btn-xs showOnWebRTC" id="webRTCConnect" style="display: none;" onclick="webRTCConnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Go Live"); ?>
    </button>
    <button class="btn btn-default btn-xs" id="startWebcam" onclick="startWebcamNow();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Use your webcam"); ?>">
        <i class="fas fa-camera"></i> <span class="hidden-sm hidden-xs"><?php echo __("Webcam"); ?></span>
    </button>
</span>
<script src="<?php echo getCDN(); ?>plugin/Live/webRTC.js" type="text/javascript"></script>
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
            $('#divWebcamIFrame, .showOnWebRTC').slideUp();
        }
        var updateControlStatusLastState;
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