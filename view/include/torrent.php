<script src="<?php echo $global['webSiteRootURL']; ?>view/js/webtorrent/webtorrent.min.js" type="text/javascript"></script>
<?php
$playNowVideo = $video;
$transformation = "{rotate:" . $video['rotation'] . ", zoom: " . $video['zoom'] . "}";

if ($video['rotation'] === "90" || $video['rotation'] === "270") {
    $aspectRatio = "9:16";
    $vjsClass = "vjs-9-16";
    $embedResponsiveClass = "embed-responsive-9by16";
} else {
    $aspectRatio = "16:9";
    $vjsClass = "vjs-16-9";
    $embedResponsiveClass = "embed-responsive-16by9";
}
?>
<div class="row main-video" id="mvideo">
    <div class="col-sm-2 col-md-2 firstC"></div>
    <div class="col-sm-8 col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs"
                        onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="far fa-window-close"></i>
                </button>
            </div>
            <div id="main-video" class="embed-responsive <?php echo $embedResponsiveClass; ?>">
                <video
                <?php if ($config->getAutoplay() && false) { // disable it for now ?>
                        autoplay="true"
                        muted="muted"
                    <?php } ?>
                    preload="auto"
                    poster="<?php echo $poster; ?>" controls class="embed-responsive-item video-js vjs-default-skin <?php echo $vjsClass; ?> vjs-big-play-centered" id="mainVideo" data-setup='{ "aspectRatio": "<?php echo $aspectRatio; ?>" }'>

                    <p><?php echo __("If you can't view this video, your browser does not support HTML5 videos"); ?></p>
                    <p class="vjs-no-js"><?php echo __("To view this video please enable JavaScript, and consider upgrading to a web browser that"); ?>
                        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                    </p>
                </video>
                <?php
                require_once $global['systemRootPath'] . 'plugin/YouPHPTubePlugin.php';
                // the live users plugin
                if (YouPHPTubePlugin::isEnabled("0e225f8e-15e2-43d4-8ff7-0cb07c2a2b3b")) {
                    require_once $global['systemRootPath'] . 'plugin/VideoLogoOverlay/VideoLogoOverlay.php';
                    $style = VideoLogoOverlay::getStyle();
                    $url = VideoLogoOverlay::getLink();
                    ?>
                    <div style="<?php echo $style; ?>">
                        <a href="<?php echo $url; ?>"> <img src="<?php echo $global['webSiteRootURL']; ?>videos/logoOverlay.png" class="img-responsive col-lg-12 col-md-8 col-sm-7 col-xs-6"></a>
                    </div>
                <?php } ?>

            </div>
        </div>
        <?php if ($config->getAllow_download()) { ?>
            <?php if ($playNowVideo['type'] == "video") { ?>
                <a class="btn btn-xs btn-default pull-right " role="button" href="<?php echo $global['webSiteRootURL'] . "videos/" . $playNowVideo['filename']; ?>.mp4" download="<?php echo $playNowVideo['title'] . ".mp4"; ?>" >
                    <i class="fa fa-download"></i>
                    <?php echo __("Download video"); ?>
                </a>
            <?php } else { ?>
                <a class="btn btn-xs btn-default pull-right " role="button" href="<?php echo $video['videoLink']; ?>" download="<?php echo $playNowVideo['title'] . ".mp4"; ?>" >
                    <i class="fa fa-download"></i>
                    <?php echo __("Download video"); ?>
                </a>

                <?php
            }
        }
        ?>
    </div>
    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
<script>
<?php $_GET['isMediaPlaySite'] = $playNowVideo['id']; ?>

    var mediaId = <?php echo $playNowVideo['id']; ?>;
    var client = new WebTorrent();

    var torrentId = 'magnet:?xt=urn:btih:08ada5a7a6183aae1e09d831df6748d566095a10&dn=Sintel&tr=udp%3A%2F%2Ftracker.coppersurfer.tk%3A6969&tr=udp%3A%2F%2Ftracker.empire-js.us%3A1337&tr=udp%3A%2F%2Ftracker.leechers-paradise.org%3A6969&tr=udp%3A%2F%2Ftracker.opentrackr.org%3A1337&tr=wss%3A%2F%2Ftracker.openwebtorrent.com&ws=https%3A%2F%2Fwebtorrent.io%2Ftorrents%2F&xs=https%3A%2F%2Fwebtorrent.io%2Ftorrents%2Fsintel.torrent';
    var player;

    $(document).ready(function () {
      client.add(torrentId, function (torrent) {
        // Torrents can contain many files. Let's use the .mp4 file
        var file = torrent.files.find(function (file) {
          return file.name.endsWith('.mp4');
        });

        // Display the file by adding it to the DOM. Supports video, audio, image, etc. files

        file.appendTo('#main-video');
        //file.appendTo('#mainVideo');
        //file.renderTo('#mainVideo_html5_api');
      });
      // workaround until integration into videojs works
      $("#mainVideo").hide();
<?php
if (!$config->getAllow_download()) {
    ?>
            // Prevent HTML5 video from being downloaded (right-click saved)?
            $('#mainVideo').bind('contextmenu', function () {
                return false;
            });
<?php } ?>
        player = videojs('mainVideo');
        player.zoomrotate(<?php echo $transformation; ?>);
        player.on('play', function () {
            addView(<?php echo $playNowVideo['id']; ?>);
        });
        player.ready(function () {



<?php if ($config->getAutoplay()) {
    ?>
                setTimeout(function () {
                    if (typeof player === 'undefined') {
                        player = videojs('mainVideo');
                    }
                    try {
                        player.play();
                    } catch (e) {
                        setTimeout(function () {
                            player.play();
                        }, 1000);
                    }
                }, 150);
<?php } else {
    ?>
                if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                    setTimeout(function () {
                        if (typeof player === 'undefined') {
                            player = videojs('mainVideo');
                        }
                        try {
                            player.play();
                        } catch (e) {
                            setTimeout(function () {
                                player.play();
                            }, 1000);
                        }
                    }, 150);
                }
<?php }
?>
            this.on('ended', function () {
                console.log("Finish Video");
<?php
// if autoplay play next video
if (!empty($autoPlayVideo)) {
    ?>
                    if (Cookies.get('autoplay') && Cookies.get('autoplay') !== 'false') {
                        document.location = '<?php echo $autoPlayVideo['url']; ?>';
                    }
<?php } ?>

            });
        });
        player.persistvolume({
            namespace: "YouPHPTube"
        });

        // in case the video is muted
        setTimeout(function () {
            if (player.muted()) {
                swal({
                    title: "<?php echo __("Your Media is Muted"); ?>",
                    text: "<?php echo __("Would you like to unmute it?"); ?>",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "<?php echo __("Yes, unmute it!"); ?>",
                    closeOnConfirm: true
                },
                        function () {
                            player.muted(false);
                        });
            }
        }, 500);

    });
</script>
