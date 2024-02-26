<?php
$sources = getVideosURLIMAGE($video['filename']);
//var_dump($sources);exit;
?><div class="row main-video" style="padding: 10px;" id="mvideo">
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
            <video id="mainVideo" style="display: none; height: 0;width: 0;"></video>
            <?php
            if (AVideoPlugin::isEnabledByName('ImageGallery') && !empty(ImageGallery::listFiles($video['id']))) {
            ?>
                <!-- ImageGallery <?php echo basename(__FILE__); ?> -->
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe class="embed-responsive-item" src="<?php echo $global['webSiteRootURL']; ?>plugin/ImageGallery/?avideoIframe=1&videos_id=<?php echo $video['id']; ?>" allowfullscreen>
                    </iframe>
                </div>
            <?php
            } else {
            ?>
                <center>
                    <img src="<?php
                                echo $sources["image"]['url']
                                ?>" class="img img-responsive" style="max-height: 600px;">
                </center>
            <?php
            }
            ?>
            <script>
                $(document).ready(function() {
                    addView(<?php echo $video['id']; ?>, 0);
                });
            </script>

        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

    });
</script>
<div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->