 <script src="<?php echo $global['webSiteRootURL']; ?>/js/videojs-wavesurfer/wavesurfer.min.js"></script>
  <script src="<?php echo $global['webSiteRootURL']; ?>/js/videojs-wavesurfer/videojs.wavesurfer.js"></script>
<div class="row main-video" style="padding: 10px;">
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 ">
        <audio controls class="center-block video-js vjs-default-skin "  id="mainAudio" autoplay >
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.ogg" type="audio/ogg" />
            <source src="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3" type="audio/mpeg" />
            <a href="<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3">horse</a>
        </audio>
    </div> 

    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
<script>
    $(document).ready(function () {
        var player = videojs('mainAudio', {
            controls: true,
            autoplay: true,
            loop: false,
            width: 400,
            height: 225,
            plugins: {
                wavesurfer: {
                    src: '<?php echo $global['webSiteRootURL']; ?>videos/<?php echo $video['filename']; ?>.mp3',
                    msDisplayMax: 10,
                    debug: true,
                    waveColor: 'white',
                    progressColor: 'gray',
                    cursorColor: '#C00',
                    hideScrollbar: true
                }
            }
        });
    });
</script>