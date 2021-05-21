<?php
$sources = getVideosURLZIP($video['filename']);
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
            <video playsinline webkit-playsinline="webkit-playsinline"  id="mainVideo" style="display: none; height: 0;width: 0;" ></video>
            <center>
                <div class="panel panel-default">
                    <div class="panel-heading"><i class="far fa-file-archive"></i> <?php echo $video['title']; ?></div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <?php
                            $za = new ZipArchive();
                            $za->open($sources['zip']["path"]);
                            for ($i = 0; $i < $za->numFiles; $i++) {
                                $stat = $za->statIndex($i);
                                $fname = basename($stat['name']);
                                ?>
                            <li class="list-group-item" style="text-align: left;"><i class="<?php echo fontAwesomeClassName($fname) ?>"></i> <?php echo $fname; ?></li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </center>
            <script>
                $(document).ready(function () {
                    addView(<?php echo $video['id']; ?>, 0);
                });
            </script>

        </div>
    </div>
</div>
<script>
    $(document).ready(function () {

    });
</script>
<div class="col-xs-12 col-sm-12 col-lg-2"></div>
</div><!--/row-->
