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
    $(document).ready(function () {
    });
</script>