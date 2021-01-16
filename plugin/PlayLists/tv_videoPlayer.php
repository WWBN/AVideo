<link href="<?php echo $global['webSiteRootURL']; ?>view/js/video.js/video-js.min.css" rel="stylesheet" type="text/css"/>
<style>
    #mainVideo{
        width: 100vw;
        height: 100vh; 
        border:none;
        overflow:hidden;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #000;
    }
    #volumeBar{
        position: fixed;
        left: 25px;
        bottom: 25px;
        width: 50px;
    }
    #volumeBar .volumeDot{
        margin: 5px;
        background-color: #FFF;
        box-shadow: 2px 2px 4px #000000;
    }

    #volumeBar .volume10{
        background-color: #2a9fd6;
    }
    <?php
    for ($i = 9; $i > 0; $i--) {
        ?>
        .volume<?php echo $i; ?>{
            opacity: <?php echo 0.1 * $i; ?>;
        }
        <?php
    }
    ?>
    .volume0{
        opacity: 0.05;
    }
</style>
<?php
include $global['systemRootPath'] . 'view/include/video.min.js.php';
?>

<video poster="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/OnAir.jpg" playsinline webkit-playsinline="webkit-playsinline" 
       class="video-js vjs-default-skin vjs-big-play-centered" 
       id="mainVideo" style="display: none;">
    <source src="<?php echo $global['webSiteRootURL']; ?>plugin/Live/view/loopBGHLS/index.m3u8" type='application/x-mpegURL'>
</video>

<div id="volumeBar" style="display: none;">
    <?php
    for ($i = 10; $i >= 0; $i--) {
        echo "<div class=' volumeDot volume{$i}' >&nbsp;</div>";
    }
    ?>
</div>

<script>
    var isIframe = false;
    function loadLiveVideo(sourceLink) {
        showChannelTop();
        if (isVideoOpened()) {
            console.log('loadLiveVideo::isVideoOpened', sourceLink);
            var currentSource = player.currentSources()
            if (currentSource[0].src !== sourceLink) {
                player.src({
                    src: sourceLink,
                    type: "application/x-mpegURL"});
                player.play();
            }
        } else {
            player.src({
                src: sourceLink,
                type: "application/x-mpegURL"});

            console.log('loadLiveVideo', sourceLink);
            $('#mainVideo, #mainVideo video').fadeIn('slow', function () {
                player.play();
                $('body').addClass('showingVideo');
            });
            undoArray.push("closeLiveVideo();");
        }
    }

    function closeLiveVideo() {
        $('#channelTop').fadeOut('slow');
        $('#mainVideo').fadeOut('slow', function () {
            player.src({
                src: loopBGHLS,
                type: "application/x-mpegURL"});
            player.pause();
            $('body').removeClass('showingVideo');
        });
    }

    function focusVideo() {
        $('#mainVideo').focus();
    }
    player = videojs('mainVideo', {errorDisplay: false, html5: {nativeAudioTracks: false, nativeVideoTracks: false, hls: {overrideNative: true}}, liveui: true});
</script>