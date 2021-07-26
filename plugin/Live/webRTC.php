<?php
$lObj = AVideoPlugin::getDataObject('Live');
$iframeURL = $lObj->webRTC_player;
$iframeURL = addQueryStringParameter($iframeURL, 'webSiteRootURL', $global['webSiteRootURL']);
$iframeURL = addQueryStringParameter($iframeURL, 'userHash', Live::getUserHash(User::getId()));
?>
<span class=" pull-right" style="margin-left: 5px;">
    <button class="btn btn-danger btn-xs hideOnNonWebRTC" id="webRTCDisconnect" style="display: none;" onclick="webRTCDisconnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Stop"); ?>">
        <i class="fas fa-stop"></i> <?php echo __("Stop"); ?>
    </button>
    <button class="btn btn-success btn-xs hideOnNonWebRTC" id="webRTCConnect" style="display: none;" onclick="webRTCConnect();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Start Live Now"); ?>">
        <i class="fas fa-circle"></i> <?php echo __("Go Live"); ?>
    </button>
    <button class="btn btn-primary btn-xs showOnWebRTC" style="display: none;" onclick="webRTCConfiguration();" data-toggle="tooltip" data-placement="bottom" title="<?php echo __("Configuration"); ?>">
        <i class="fas fa-cog"></i> <span class="hidden-sm hidden-xs"><?php echo __("Configuration"); ?></span>
    </button>
    <div class="dropdown" style="display: inline;">
        <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
            <i class="fas fa-camera"></i> <?php echo __("Webcam"); ?>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
            <li><a href="<?php echo "{$global['webSiteRootURL']}plugin/Live/webcamFullscreen.php"; ?>"><i class="fas fa-expand"></i> Fullscreen</a></li>
            <li><a href="#" onclick="toogleWebcam();" ><i class="fas fa-cog"></i> Advanced</a></li>
        </ul>
    </div>
</span>
<script src="<?php echo getCDN(); ?>plugin/Live/webRTC.js" type="text/javascript"></script>
<script>
                var updateControlStatusLastState;
                function toogleWebcam() {
                    updateControlStatusLastState = null;
                    isVisible = $('#divWebcamIFrame').is(":visible");
                    $('#webRTCConnect, #webRTCDisconnect').hide();
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
                    $('.showOnWebRTC, .hideOnNonWebRTC').hide();
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
                    }, 500);
                });
</script>