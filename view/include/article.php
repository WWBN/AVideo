<?php
$sources = getVideosURLPDF($video['filename']);
//var_dump($sources);exit;
?>
<div class="row main-video ypt-main-article" style="padding: 10px;" id="mvideo">
    <div class="col-xs-12 col-sm-12 col-lg-2 firstC"></div>
    <div class="col-xs-12 col-sm-12 col-lg-8 secC">

        <div id="videoContainer ypt-article-container">
            <div id="floatButtons" style="display: none;">
                <p class="btn btn-outline btn-xs move">
                    <i class="fas fa-expand-arrows-alt"></i>
                </p>
                <button type="button" class="btn btn-outline btn-xs" onclick="closeFloatVideo();floatClosed = 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <div id="main-video" class="bgWhite list-group-item ypt-article" style="max-height: 80vh; overflow: hidden; overflow-y: auto; font-size: 1.5em;">
                <h1 style="font-size: 1.5em; font-weight: bold; text-transform: uppercase; border-bottom: #CCC solid 1px;">
                    <?php
                    echo $video['title'];
                    ?>
                </h1>
                <?php
                echo Video::htmlDescription($video['description']);
                ?>     
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
    <script>
        $(document).ready(function () {

        });
    </script>
    <div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
