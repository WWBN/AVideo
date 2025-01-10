<div class="row showWhenIsNotLive">
    <div class="col-sm-10">
        <button type="button" id="startLive" class="btn btn-danger btn-block" onclick="startWebcamLive(rtmpURL);">
            <i class="fa fa-play"></i> Start Live
        </button>

    </div>
    <div class="col-sm-2">
        <button type="button" class="btn btn-default btn-block" onclick="$('#mediaSelector').fadeToggle()">
        <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

    </div>
</div>
<button type="button" id="stopLive" class="btn btn-dark showWhenIsLive  btn-block" onclick="stopWebcamLive(rtmpURL);">
    <i class="fa fa-stop"></i> Stop Live
</button>