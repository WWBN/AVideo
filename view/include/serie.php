<?php
global $isSerie;
$isSerie = 1;

$link = "{$global['webSiteRootURL']}plugin/PlayLists/embed.php";
$link = addQueryStringParameter($link, 'playlists_id', $video['serie_playlists_id']);
$link = addQueryStringParameter($link, 'autoplay', $config->getAutoplay());
$link = addQueryStringParameter($link, 'playlist_index', @$_REQUEST['playlist_index']);

?>
<!-- serie -->
<div class="row main-video" id="mvideo">
    <div class="col-md-2 firstC"></div>
    <div class="col-md-8 secC">
        <div id="videoContainer">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <div id="main-video" class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" scrolling="no" allowfullscreen="true" src="<?php echo $link; ?>"></iframe>
                <script>
                    $(document).ready(function () {
                        addView(<?php echo $video['id']; ?>, 0);
                    });
                </script>

            </div>


            <?php
            showCloseButton();
            ?>
        </div>
    </div>

    <div class="col-sm-2 col-md-2"></div>
</div>
<!--/row-->
