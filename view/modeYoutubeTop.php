<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12 AdsLeaderBoard AdsLeaderBoardTop">
        <center style="margin:5px;">
            <?php
            $getAdsLeaderBoardTop = getAdsLeaderBoardTop();
            if (!empty($getAdsLeaderBoardTop)) {
                ?>
                <style>
                    .compress {
                        top: 100px !important;
                    }
                </style>
                <?php
                echo $getAdsLeaderBoardTop;
            }
            ?>
        </center>
    </div>
</div>
<?php
$vType = Video::getIncludeType($video);

require "{$global['systemRootPath']}view/include/{$vType}.php";
if(!empty($modeYouTubeTime)){
    $modeYouTubeTimeLog['After include video ' . $vType] = microtime(true) - $modeYouTubeTime;
}
$modeYouTubeTime = microtime(true);
?>
<div class="row">
    <div class="col-sm-1 col-md-1"></div>
    <div class="col-sm-10 col-md-10 AdsLeaderBoard AdsLeaderBoardTop2">
        <center style="margin:5px;">
            <?php echo getAdsLeaderBoardTop2(); ?>
        </center>
    </div>
</div>