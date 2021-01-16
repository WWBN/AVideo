<div class="panel panel-default">
    <div class="panel-heading"><?php echo __("WebCam Streaming"); ?></div>
    <div class="panel-body">
        <div class="embed-responsive embed-responsive-16by9">
            <div class="embed-responsive-item"  id="webcam">
                <button class="btn btn-primary btn-block" id="enableWebCam">
                    <i class="fa fa-camera"></i> <?php echo __("Enable WebCam Stream"); ?>
                </button>
                <div class="alert alert-warning">
                    <i class="fa fa-warning"><?php echo __("We will check if there is a stream conflict before stream"); ?></i>
                </div>

                <div class="alert alert-info">
                    <?php echo __("This is an experimental resource"); ?>
                </div>
            </div>
        </div>
    </div>
</div>