<div class="text-center showWhenIsNotLive" style="display: none;">
    <button 
        type="button" 
        id="startLive" 
        class="btn btn-success oval-menu animate__animated animate__bounceIn showWhenWebCamIsOn" 
        onclick="startWebcamLive(rtmpURLEncrypted);" 
        title="<?php echo __('Click here to go live!'); ?>" data-toggle="tooltip" >
        <i class="fa fa-play"></i> Go Live
    </button>

    <button 
        type="button" 
        id="stopWebRTC" 
        class="btn btn-danger oval-menu animate__animated animate__bounceIn showWhenWebCamIsOn" 
        style="-webkit-animation-delay: .2s; animation-delay: .2s;" 
        title="<?php echo __('Stop your webcam'); ?>" data-toggle="tooltip" >
        <div>
            <i class="fa-solid fa-camera"></i>
            <i class="fa-solid fa-slash" style="position: absolute; left: 10px; top: 8px;"></i>
        </div>
    </button>

    <button 
        type="button" 
        id="startWebRTC" 
        class="btn btn-success oval-menu animate__animated animate__bounceIn showWhenWebCamIsOff" 
        style="-webkit-animation-delay: .2s; animation-delay: .2s;" 
        title="<?php echo __('Start your webcam'); ?>" data-toggle="tooltip" >
        <i class="fa-solid fa-camera"></i>
    </button>

    <button 
        type="button" 
        class="btn btn-default oval-menu animate__animated animate__bounceIn" 
        onclick="toggleMediaSelector();" 
        style="-webkit-animation-delay: .4s; animation-delay: .4s;" 
        title="<?php echo __('Change your camera or microphone settings'); ?>" data-toggle="tooltip" >
        <i class="fa-solid fa-gear"></i>
    </button>
</div>

<div class="text-center showWhenIsLive" style="display: none;">
    <button 
        type="button" 
        id="stopLive" 
        class="btn btn-danger oval-menu animate__animated animate__bounceIn" 
        onclick="stopWebcamLive(rtmpURLEncrypted);" 
        title="<?php echo __('Stop your live video'); ?>" data-toggle="tooltip" >
        <i class="fa fa-stop"></i> Stop
    </button>
</div>
