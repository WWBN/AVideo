<div class="row main-video" style="padding: 10px;" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <video id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <center>
                <img src="<?php
                echo ImagesPlaceHolders::getVideoNotFoundPoster(ImagesPlaceHolders::$RETURN_URL);
                ?>" class="img img-responsive ImagesPlaceHoldersDefaultImage"  style="max-height: 600px;" >
            </center>
        </div>
    </div>
</div>
<div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
