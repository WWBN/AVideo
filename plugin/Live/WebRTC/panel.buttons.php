<div class="text-center showWhenIsNotLive" style="display: none;">
    <button type="button" id="startLive" class="btn btn-success oval-menu animate__animated animate__bounceIn" onclick="startWebcamLive(rtmpURLEncrypted);">
        <i class="fa fa-play"></i> Go Live
    </button>
    <button type="button" class="btn btn-default oval-menu animate__animated animate__bounceIn" onclick="$('#mediaSelector').fadeToggle()" style=" -webkit-animation-delay: .2s; animation-delay: .2s;">
        <i class="fa-solid fa-ellipsis-vertical"></i>
    </button>
</div>
<div class="text-center showWhenIsLive" style="display: none;">
    <button type="button" id="stopLive" class="btn btn-danger  oval-menu animate__animated animate__bounceIn" onclick="stopWebcamLive(rtmpURLEncrypted);">
        <i class="fa fa-stop"></i> Stop
    </button>
</div>