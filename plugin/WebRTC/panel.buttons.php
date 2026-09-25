<div class="webrtc-actions" id="webcamMediaControls">
    <button type="button" id="startWebRTC" class="btn btn-primary" onclick="prepareWebcam();">
        <i class="fa-solid fa-camera" aria-hidden="true"></i> <?php echo __('Prepare camera and microphone'); ?>
    </button>
    <button type="button" id="startLive" class="btn btn-success hidden" onclick="startWebcamLive(rtmpURLEncrypted);">
        <i class="fa fa-play" aria-hidden="true"></i> <?php echo __('Start broadcast'); ?>
    </button>
    <button type="button" id="stopLive" class="btn btn-danger hidden" onclick="stopWebcamLive(rtmpURLEncrypted);">
        <i class="fa fa-stop" aria-hidden="true"></i> <?php echo __('End broadcast'); ?>
    </button>
    <button type="button" id="stopWebRTC" class="btn btn-default hidden" onclick="stopWebRTC();">
        <i class="fa-solid fa-video-slash" aria-hidden="true"></i> <?php echo __('Turn off preview'); ?>
    </button>
    <button type="button" id="retryWebRTC" class="btn btn-primary hidden" onclick="retryWebRTC();">
        <i class="fa fa-refresh" aria-hidden="true"></i> <?php echo __('Try again'); ?>
    </button>
</div>
