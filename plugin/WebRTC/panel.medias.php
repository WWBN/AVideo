<div class="clearfix" style="display: none; margin-bottom: 10px;" id="mediaSelector">
    <!-- Video Input Selection -->
    <div class="col-sm-3 col-xs-6">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-video-camera"></i></span>
            <select id="videoSource" class="form-control">
                <option value="">Select Video Source</option>
            </select>
        </div>
    </div>
    <!-- Audio Input Selection -->
    <div class="col-sm-3 col-xs-6">
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-microphone"></i></span>
            <select id="audioSource" class="form-control">
                <option value="">Select Audio Source</option>
            </select>
        </div>
    </div>
    <!-- Screen Share Button -->
    <div class="col-sm-3 col-xs-6">
        <button type="button" id="startScreenShare" class="btn btn-primary btn-block">
            <i class="fa fa-desktop"></i> Screen
        </button>
    </div>
    <div class="col-sm-3 col-xs-6">
        <button type="button" id="applyChanges" class="btn btn-success btn-block ">
            <i class="fa fa-check"></i>
        </button>
    </div>
</div>