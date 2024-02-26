<div class="main-video" id="mvideo">
    <div class="row">
        <div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0 text-center" id="videoCol">
            <div id="videoContainer">
                <div id="main-video" class="embed-responsive embed-responsive-16by9">
                    <?php
                    echo $htmlMediaTag;
                    ?>
                </div>
                <div id="floatButtons" style="display: none;">
                    <button type="button" class="btn btn-default btn-circle" 
                    onclick="closeFloatVideo(); floatClosed = 1;" id="floatButtonsClose">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php
                echo showCloseButton();
                ?>
            </div>
        </div>
    </div>
</div>