<div class="row main-video" style="padding: 10px;">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 ">
        <audio controls class="center-block"  id="mainAudio">
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" />
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
        </audio>
        <script>
            var playCount = 0;
            $('#mainAudio').bind('play', function (e) {
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