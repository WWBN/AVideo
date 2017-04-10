
<div class="row main-video">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8">
        <div align="center" class="embed-responsive embed-responsive-16by9">
            <video poster="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.jpg" controls crossorigin class="embed-responsive-item" id="mainVideo">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp4" type="video/mp4">
                <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.webm" type="video/webm">
                <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
            </video>
        </div>
        <script>
            var playCount = 0;
            $('#mainVideo').bind('play', function (e) {
                playCount++;
                if (playCount == 1) {
                    $.ajax({
                        url: '<?php echo $global['webSiteRootURL']; ?>addViewCountVideo',
                        method: 'post',
                        data: {'id': "<?php echo $video['id']; ?>"}
                    });

                }
            });
        </script>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->